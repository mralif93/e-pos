<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Outlet;
use App\Models\StockLedger;
use App\Domains\Inventory\Services\StockValuationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockValuationTest extends TestCase
{
    use RefreshDatabase;

    protected StockValuationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockValuationService();
    }

    public function test_avco_calculation_is_accurate()
    {
        $product = Product::factory()->create(['cost' => 10.00]);
        $outlet = Outlet::factory()->create();

        // 1. Purchase 10 items at RM 10.00
        StockLedger::create([
            'product_id' => $product->id,
            'outlet_id' => $outlet->id,
            'quantity' => 10,
            'unit_cost' => 10.00,
            'type' => 'PURCHASE'
        ]);

        // 2. Purchase 10 items at RM 20.00
        StockLedger::create([
            'product_id' => $product->id,
            'outlet_id' => $outlet->id,
            'quantity' => 10,
            'unit_cost' => 20.00,
            'type' => 'PURCHASE'
        ]);

        // Total Cost: (10 * 10) + (10 * 20) = 300
        // Total Qty: 20
        // Expected AVCO: 300 / 20 = RM 15.00

        $avco = $this->service->getAverageCostValue($product->id, $outlet->id);
        $this->assertEquals(15.00, $avco);
    }

    public function test_fifo_valuation_is_accurate()
    {
        $product = Product::factory()->create(['cost' => 10.00]);
        $outlet = Outlet::factory()->create();

        // Batch 1: 10 items at RM 10.00
        StockLedger::create([
            'product_id' => $product->id,
            'outlet_id' => $outlet->id,
            'quantity' => 10,
            'unit_cost' => 10.00,
            'type' => 'PURCHASE',
            'created_at' => now()->subDays(2)
        ]);

        // Batch 2: 10 items at RM 20.00
        StockLedger::create([
            'product_id' => $product->id,
            'outlet_id' => $outlet->id,
            'quantity' => 10,
            'unit_cost' => 20.00,
            'type' => 'PURCHASE',
            'created_at' => now()->subDay()
        ]);

        // Current stock is 15 items.
        // FIFO logic: 
        // 10 items from Batch 2 (most recent) = 10 * 20 = 200
        // 5 items from Batch 1 (older) = 5 * 10 = 50
        // Total expected value: 250

        // Mock the current stock level by adding a sale (negative quantity)
        StockLedger::create([
            'product_id' => $product->id,
            'outlet_id' => $outlet->id,
            'quantity' => -5,
            'unit_cost' => 10.00,
            'type' => 'SALE'
        ]);

        $fifoValue = $this->service->getFifoValue($product->id, $outlet->id);
        $this->assertEquals(250.00, $fifoValue);
    }
}
