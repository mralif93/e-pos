<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_sales_history()
    {
        $outlet = Outlet::create([
            'name' => 'Test Outlet',
            'outlet_code' => 'TEST001',
            'has_pos_access' => true,
        ]);

        $user = User::factory()->create(['outlet_id' => $outlet->id, 'role' => 'staff']);

        // Create a sale
        $sale = Sale::create([
            'outlet_id' => $outlet->id,
            'user_id' => $user->id,
            'total_amount' => 100,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.pos.history'));

        $response->assertStatus(200)
            ->assertJsonFragment(['total_amount' => 100]) // Integer check
            ->assertJsonFragment(['id' => $sale->id]);
    }

    public function test_can_void_sale_with_pin()
    {
        $outlet = Outlet::create([
            'name' => 'Test Outlet',
            'outlet_code' => 'TEST001',
            'has_pos_access' => true,
        ]);

        $user = User::factory()->create(['outlet_id' => $outlet->id, 'role' => 'Manager', 'pin' => '1234']);

        $sale = Sale::create([
            'outlet_id' => $outlet->id,
            'user_id' => $user->id,
            'total_amount' => 100,
            'status' => 'completed',
        ]);

        // 1. Fail without PIN
        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.pos.void', $sale->id), [])
            ->assertStatus(422);

        // 2. Fail with wrong PIN
        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.pos.void', $sale->id), ['pin' => '0000'])
            ->assertStatus(403);

        // 3. Success with correct PIN
        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.pos.void', $sale->id), ['pin' => '1234'])
            ->assertStatus(200);

        $this->assertEquals('void', $sale->fresh()->status);
    }
}
