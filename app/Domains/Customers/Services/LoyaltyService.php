<?php

namespace App\Domains\Customers\Services;

use App\Models\Customer;
use App\Models\Sale;

class LoyaltyService
{
    protected function getPointsPerRinggit(): int
    {
        return config('services.loyalty.points_per_ringgit', 1);
    }

    protected function getMinSpendForPoints(): float
    {
        return (float) config('services.loyalty.min_spend_for_points', 1);
    }

    public function calculatePointsEarned(float $amount): int
    {
        if ($amount < $this->getMinSpendForPoints()) {
            return 0;
        }

        return (int) floor($amount * $this->getPointsPerRinggit());
    }

    public function calculateMaxRedeemablePoints(Customer $customer, float $totalAmount): int
    {
        $maxPointsValue = $totalAmount * 0.5;
        $maxPointsByValue = (int) floor($maxPointsValue / $this->getPointsValueRate($customer->loyalty_tier));
        
        return min($customer->loyalty_points, $maxPointsByValue);
    }

    public function calculateDiscountFromPoints(int $points, string $tier): float
    {
        return $points * $this->getPointsValueRate($tier);
    }

    protected function getPointsValueRate(string $tier): float
    {
        $rates = config('services.loyalty.points_value', [
            'bronze' => 0.025,
            'silver' => 0.030,
            'gold' => 0.035,
            'platinum' => 0.040,
        ]);

        return $rates[$tier] ?? $rates['bronze'];
    }

    public function processSalePoints(Sale $sale, ?int $pointsToRedeem = null): array
    {
        $result = [
            'points_earned' => 0,
            'points_redeemed' => 0,
            'discount_from_points' => 0,
        ];

        if (!$sale->customer_id) {
            return $result;
        }

        $customer = $sale->customer;
        
        if (!$customer) {
            return $result;
        }

        $subtotal = $sale->total_amount - $sale->tax_amount;

        if ($pointsToRedeem && $pointsToRedeem > 0) {
            $maxRedeemable = $this->calculateMaxRedeemablePoints($customer, $subtotal);
            $redeemPoints = min($pointsToRedeem, $maxRedeemable);
            
            $discount = $this->calculateDiscountFromPoints($redeemPoints, $customer->loyalty_tier);
            
            $customer->redeemPoints(
                $redeemPoints,
                $discount,
                $sale->id,
                "Redeemed {$redeemPoints} points for RM" . number_format($discount, 2) . " discount",
                $sale->user_id
            );

            $result['points_redeemed'] = $redeemPoints;
            $result['discount_from_points'] = $discount;
        }

        $earnableAmount = $subtotal - $result['discount_from_points'];
        $pointsEarned = $this->calculatePointsEarned($earnableAmount);

        if ($pointsEarned > 0) {
            $customer->addPoints(
                $pointsEarned,
                $sale->id,
                "Earned {$pointsEarned} points from sale",
                $sale->user_id
            );

            $result['points_earned'] = $pointsEarned;
        }

        return $result;
    }
}
