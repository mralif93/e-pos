<?php

namespace App\Repositories;

use App\Models\Sale;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class SaleRepository
{
    /**
     * Get paginated sales history for an outlet.
     */
    public function getHistoryForOutlet(int $outletId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return Sale::where('outlet_id', $outletId)
            ->with(['saleItems.product', 'user', 'customer'])
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function($query) use ($search) {
                    $query->where('id', 'like', "%{$search}%")
                          ->orWhereHas('customer', function($q) use ($search) {
                              $q->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                          })
                          ->orWhereHas('user', function($q) use ($search) {
                              $q->where('name', 'like', "%{$search}%");
                          });
                });
            })
            ->when(!empty($filters['date']), function ($q) use ($filters) {
                $q->whereDate('created_at', $filters['date']);
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a summary of sales for a specific period.
     */
    public function getSummaryForOutlet(int $outletId, Carbon $start, Carbon $end): array
    {
        $query = Sale::where('outlet_id', $outletId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end]);

        return [
            'total_revenue' => (float) $query->sum('total_amount'),
            'sale_count' => $query->count(),
            'total_tax' => (float) $query->sum('tax_amount'),
            'total_discount' => (float) $query->sum('discount_amount'),
        ];
    }
}
