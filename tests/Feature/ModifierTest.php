<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Modifier;
use App\Models\ModifierItem;

class ModifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_product_with_modifiers()
    {
        $outlet = Outlet::create(['name' => 'Test Outlet', 'address' => 'Test Address']);
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'outlet_id' => $outlet->id,
            'role' => 'admin'
        ]);

        $product = Product::create([
            'name' => 'Latte',
            'slug' => 'latte',
            'price' => 10,
            'stock_level' => 100,
            'is_active' => true,
        ]);

        $modifier = Modifier::create(['name' => 'Milk Options', 'type' => 'single']);
        $item1 = ModifierItem::create(['modifier_id' => $modifier->id, 'name' => 'Soy Milk', 'price' => 1]);
        $item2 = ModifierItem::create(['modifier_id' => $modifier->id, 'name' => 'Oat Milk', 'price' => 2]);

        $product->modifiers()->attach($modifier->id);

        \App\Models\ProductOutletPrice::create([
            'product_id' => $product->id,
            'outlet_id' => $outlet->id,
            'price' => 10,
            'cost' => 5,
            'stock_level' => 100
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('api.v1.pos.products'));

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Latte'])
            ->assertJsonFragment(['name' => 'Milk Options'])
            ->assertJsonFragment(['name' => 'Soy Milk'])
            ->assertJsonFragment(['price' => 1]) // Price is 1
            ->assertJsonFragment(['name' => 'Oat Milk']);
    }
}
