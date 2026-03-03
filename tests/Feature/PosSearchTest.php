<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Outlet;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOutletPrice;

class PosSearchTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outlet = Outlet::create(['name' => 'Test Outlet', 'address' => 'Test Address']);
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'outlet_id' => $this->outlet->id,
            'role' => 'admin'
        ]);
    }

    public function test_can_search_by_sku()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'SKU-123',
            'barcode' => 'BAR-456',
            'price' => 10,
            'stock_level' => 10
        ]);

        // Assign price
        ProductOutletPrice::create(['product_id' => $product->id, 'outlet_id' => $this->outlet->id, 'price' => 10]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.v1.pos.products', ['query' => 'SKU-123']));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Test Product']);
    }

    public function test_can_search_by_barcode()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'SKU-123',
            'barcode' => 'BAR-456',
            'price' => 10,
            'stock_level' => 10
        ]);

        // Assign price
        ProductOutletPrice::create(['product_id' => $product->id, 'outlet_id' => $this->outlet->id, 'price' => 10]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.v1.pos.products', ['query' => 'BAR-456']));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Test Product']);
    }
}
