<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\Sale;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ShiftService
{
    public function openShift(int $outletId, int $userId, float $openingCash): Shift
    {
        // Check if ANY open shift exists for this outlet
        $existingOpen = Shift::where('outlet_id', $outletId)
            ->where('status', 'open')
            ->first();
        
        if ($existingOpen) {
            throw new \Exception('There is already an active shift for this outlet (#' . $existingOpen->shift_number . '). Close it first.');
        }

        return Shift::create([
            'outlet_id' => $outletId,
            'user_id' => $userId,
            'opening_cash' => $openingCash,
            'status' => 'open',
            'opened_at' => Carbon::now(),
        ]);
    }

    public function closeShift(Shift $shift, array $data): Shift
    {
        // Recalculate everything to ensure accuracy at moment of closing
        $salesSummary = $this->getShiftSalesSummary($shift);

        $data['total_sales'] = $salesSummary['total_sales'];
        $data['transaction_count'] = $salesSummary['transaction_count'];
        $data['card_total'] = $salesSummary['card_total'];
        $data['other_total'] = $salesSummary['other_total'];
        $data['expected_cash'] = (float) $shift->opening_cash + (float) $salesSummary['cash_total'];
        $data['closed_by'] = auth()->id();

        return $shift->close($data);
    }

    public function getShiftSalesSummary(Shift $shift): array
    {
        // Get all completed sales for this outlet during the shift window
        $sales = Sale::where('outlet_id', $shift->outlet_id)
            ->whereBetween('created_at', [$shift->opened_at, $shift->closed_at ?? Carbon::now()])
            ->where('status', 'completed')
            ->with(['payments', 'user'])
            ->get();

        $totalSales = $sales->sum('total_amount');
        $transactionCount = $sales->count();
        
        $cashTotal = 0;
        $cardTotal = 0;
        $otherTotal = 0;

        // Breakdown by User
        $userBreakdown = $sales->groupBy('user_id')->map(function ($userSales) {
            $user = $userSales->first()->user;
            
            $userCash = 0;
            $userCard = 0;
            $userOther = 0;

            foreach ($userSales as $sale) {
                foreach ($sale->payments as $payment) {
                    $method = strtolower($payment->payment_method);
                    $amount = (float) $payment->amount;
                    if (str_contains($method, 'cash')) $userCash += $amount;
                    elseif (str_contains($method, 'card')) $userCard += $amount;
                    else $userOther += $amount;
                }
            }

            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'total_sales' => $userSales->sum('total_amount'),
                'transaction_count' => $userSales->count(),
                'cash_total' => $userCash,
                'card_total' => $userCard,
                'other_total' => $userOther,
            ];
        })->values();

        foreach ($sales as $sale) {
            foreach ($sale->payments as $payment) {
                $method = strtolower($payment->payment_method);
                $amount = (float) $payment->amount;
                
                if (str_contains($method, 'cash')) {
                    $cashTotal += $amount;
                } elseif (str_contains($method, 'card') || str_contains($method, 'credit')) {
                    $cardTotal += $amount;
                } else {
                    $otherTotal += $amount;
                }
            }
        }

        return [
            'total_sales' => $totalSales,
            'transaction_count' => $transactionCount,
            'cash_total' => $cashTotal,
            'card_total' => $cardTotal,
            'other_total' => $otherTotal,
            'user_breakdown' => $userBreakdown,
        ];
    }

    public function getCurrentShift(int $outletId, int $userId = null): ?Shift
    {
        // Return any open shift for this outlet
        return Shift::where('outlet_id', $outletId)
            ->where('status', 'open')
            ->first();
    }

    public function getShiftHistory(int $outletId, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        return Shift::where('outlet_id', $outletId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'closedByUser'])
            ->orderBy('opened_at', 'desc')
            ->get();
    }

    public function getShiftReport(Shift $shift): array
    {
        $salesSummary = $this->getShiftSalesSummary($shift);
        
        return [
            'shift' => $shift,
            'sales_summary' => $salesSummary,
            'cash_difference' => $shift->closing_cash - $shift->expected_cash,
            'variance_percent' => $shift->expected_cash > 0 
                ? (($shift->closing_cash - $shift->expected_cash) / $shift->expected_cash) * 100 
                : 0,
        ];
    }

    public function getAllOpenShifts(): Collection
    {
        return Shift::where('status', 'open')
            ->with(['outlet', 'user'])
            ->get();
    }
}
