<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\User;
use App\Models\Sale;
use App\Domains\Customers\Services\LoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class LoyaltyCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected LoyaltyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LoyaltyService();
        
        // Ensure consistent config for testing
        Config::set('services.loyalty.points_per_ringgit', 1);
        Config::set('services.loyalty.min_spend_for_points', 1);
        Config::set('services.loyalty.points_value', [
            'bronze' => 0.02,
            'silver' => 0.03,
            'gold' => 0.04,
            'platinum' => 0.05,
        ]);
    }

    public function test_points_earned_calculation()
    {
        // RM 100 spent should earn 100 points
        $points = $this->service->calculatePointsEarned(100.00);
        $this->assertEquals(100, $points);

        // RM 0.50 spent should earn 0 points (min spend 1.00)
        $points = $this->service->calculatePointsEarned(0.50);
        $this->assertEquals(0, $points);
    }

    public function test_redemption_discount_calculation()
    {
        // 100 points for Gold tier (0.04 rate) = RM 4.00
        $discount = $this->service->calculateDiscountFromPoints(100, 'gold');
        $this->assertEquals(4.00, $discount);

        // 100 points for Bronze tier (0.02 rate) = RM 2.00
        $discount = $this->service->calculateDiscountFromPoints(100, 'bronze');
        $this->assertEquals(2.00, $discount);
    }

    public function test_max_redeemable_points_is_capped_at_50_percent_of_total()
    {
        $customer = Customer::factory()->create([
            'loyalty_points' => 10000, // Has a lot of points
            'loyalty_tier' => 'platinum' // 0.05 rate
        ]);

        // Sale Total: RM 100.00
        // 50% Cap = RM 50.00
        // Points needed for RM 50.00 @ 0.05 rate = 1000 points
        
        $maxPoints = $this->service->calculateMaxRedeemablePoints($customer, 100.00);
        $this->assertEquals(1000, $maxPoints);
    }
}
