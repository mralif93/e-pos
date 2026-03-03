<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_transaction_can_be_processed()
    {
        // 1. Setup Data
        $outlet = Outlet::create([
            'name' => 'Test Outlet',
            'outlet_code' => 'TEST001',
            'has_pos_access' => true,
        ]);

        $user = User::factory()->create([
            'outlet_id' => $outlet->id,
            'role' => 'staff'
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100,
            'stock_level' => 100,
            'is_active' => true,
        ]);

        $payload = [
            'outlet_id' => $outlet->id,
            'user_id' => $user->id,
            'customer_id' => null,
            'total_amount' => 200,
            'tax_amount' => 0,
            'status' => 'completed',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => 100
                ]
            ],
            'payments' => [
                [
                    'amount' => 200,
                    'payment_method' => 'cash'
                ]
            ]
        ];

        // 2. Act
        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.pos.sales'), $payload);

        // 3. Assert
        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data']);

        $this->assertDatabaseHas('sales', [
            'outlet_id' => $outlet->id,
            'user_id' => $user->id,
            'total_amount' => 200,
        ]);

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100,
        ]);

        $this->assertDatabaseHas('payments', [
            'amount' => 200,
            'payment_method' => 'cash',
        ]);
    }
}
