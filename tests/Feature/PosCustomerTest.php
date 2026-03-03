<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_customers()
    {
        $outlet = Outlet::create([
            'name' => 'Test Outlet',
            'outlet_code' => 'TEST001',
            'has_pos_access' => true,
        ]);

        $user = User::factory()->create(['outlet_id' => $outlet->id, 'role' => 'staff']);

        Customer::create([
            'name' => 'John Doe',
            'phone' => '0123456789',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.pos.customers.search', ['query' => 'john']));

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'John Doe'])
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_customer()
    {
        $outlet = Outlet::create([
            'name' => 'Test Outlet',
            'outlet_code' => 'TEST001',
            'has_pos_access' => true,
        ]);

        $user = User::factory()->create(['outlet_id' => $outlet->id, 'role' => 'staff']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.pos.customers.create'), [
                'name' => 'Jane Doe',
                'phone' => '0987654321',
                'email' => 'jane@example.com',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Jane Doe']);

        $this->assertDatabaseHas('customers', [
            'phone' => '0987654321',
            'created_by' => $user->id,
        ]);
    }
}
