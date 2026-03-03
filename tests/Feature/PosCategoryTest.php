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

class PosCategoryTest extends TestCase
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
            'role' => 'admin',
        ]);
    }

    public function test_can_fetch_categories()
    {
        $cat1 = Category::create(['name' => 'Coffee', 'slug' => 'coffee', 'sort_order' => 1]);
        $cat2 = Category::create(['name' => 'Food', 'slug' => 'food', 'sort_order' => 2]);

        // API only returns categories with active products + prices for the outlet
        $p1 = Product::create(['category_id' => $cat1->id, 'name' => 'P1', 'slug' => 'p1', 'price' => 10, 'is_active' => true]);
        $p2 = Product::create(['category_id' => $cat2->id, 'name' => 'P2', 'slug' => 'p2', 'price' => 20, 'is_active' => true]);

        ProductOutletPrice::create(['product_id' => $p1->id, 'outlet_id' => $this->outlet->id, 'price' => 10]);
        ProductOutletPrice::create(['product_id' => $p2->id, 'outlet_id' => $this->outlet->id, 'price' => 20]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.v1.pos.categories'));

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Coffee']);
    }

    public function test_can_filter_products_by_category()
    {
        $category1 = Category::create(['name' => 'Coffee', 'slug' => 'coffee']);
        $category2 = Category::create(['name' => 'Food', 'slug' => 'food']);

        $product1 = Product::create([
            'category_id' => $category1->id,
            'name' => 'Latte',
            'slug' => 'latte',
            'price' => 10,
            'stock_level' => 100
        ]);
        $product2 = Product::create([
            'category_id' => $category2->id,
            'name' => 'Sandwich',
            'slug' => 'sandwich',
            'price' => 15,
            'stock_level' => 100
        ]);

        // Assign prices
        ProductOutletPrice::create(['product_id' => $product1->id, 'outlet_id' => $this->outlet->id, 'price' => 10]);
        ProductOutletPrice::create(['product_id' => $product2->id, 'outlet_id' => $this->outlet->id, 'price' => 15]);

        // Filter by Category 1
        $response = $this->actingAs($this->user)
            ->getJson(route('api.v1.pos.products', ['category_id' => $category1->id]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Latte'])
            ->assertJsonMissing(['name' => 'Sandwich']);
    }
}
