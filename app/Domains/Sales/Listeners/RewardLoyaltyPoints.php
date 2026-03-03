<?php

namespace App\Domains\Sales\Listeners;

use App\Domains\Sales\Events\SaleCompleted;
use App\Domains\Customers\Services\LoyaltyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RewardLoyaltyPoints implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(SaleCompleted $event): void
    {
        $sale = $event->sale;
        $pointsToRedeem = $event->pointsToRedeem;

        if (!$sale->customer_id) {
            return;
        }

        try {
            $loyaltyService = new LoyaltyService();
            $pointsResult = $loyaltyService->processSalePoints($sale, $pointsToRedeem);

            $sale->update([
                'points_earned' => $pointsResult['points_earned'],
                'points_redeemed' => $pointsResult['points_redeemed'],
                'discount_from_points' => $pointsResult['discount_from_points'],
            ]);

            Log::info("Loyalty points rewarded for Sale #{$sale->id}", $pointsResult);
        } catch (\Exception $e) {
            Log::error("Failed to reward loyalty points for Sale #{$sale->id}: " . $e->getMessage());
            // Optionally: Handle retry logic if needed
        }
    }
}
