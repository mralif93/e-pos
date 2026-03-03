<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Outlet;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Each product is assigned ONLY to its specific outlet.
     * This ensures the outlet filter on the admin product page shows distinct product lists.
     */
    public function run(): void
    {
        $categories = Category::all();
        $outlets = Outlet::all();

        // Products defined per outlet name — each product belongs ONLY to that outlet
        $outletProducts = [
            'Cafe Delight' => [
                ['name' => 'Spanish Latte', 'cat' => 'Signature Coffee', 'price' => 13.90, 'cost' => 4.50, 'stock' => 100],
                ['name' => 'Salted Caramel Macchiato', 'cat' => 'Signature Coffee', 'price' => 14.50, 'cost' => 5.00, 'stock' => 80],
                ['name' => 'Rose Bandung Latte', 'cat' => 'Signature Coffee', 'price' => 12.90, 'cost' => 4.00, 'stock' => 60],
                ['name' => 'Espresso', 'cat' => 'Espresso Bar', 'price' => 8.00, 'cost' => 2.00, 'stock' => 150],
                ['name' => 'Cafe Latte', 'cat' => 'Espresso Bar', 'price' => 11.00, 'cost' => 3.50, 'stock' => 120],
                ['name' => 'Matcha Latte', 'cat' => 'Tea & Refreshers', 'price' => 13.00, 'cost' => 4.50, 'stock' => 90],
                ['name' => 'Butter Croissant', 'cat' => 'Pastries & Desserts', 'price' => 7.50, 'cost' => 3.00, 'stock' => 50],
                ['name' => 'Grilled Chicken Chop', 'cat' => 'Main Courses', 'price' => 22.90, 'cost' => 9.00, 'stock' => 40],
            ],
            'Fashion Boutique' => [
                ['name' => 'Classic White Shirt', 'cat' => "Men's Wear", 'price' => 89.90, 'cost' => 30.00, 'stock' => 50],
                ['name' => 'Slim Fit Jeans', 'cat' => "Men's Wear", 'price' => 129.90, 'cost' => 45.00, 'stock' => 40],
                ['name' => 'Summer Floral Dress', 'cat' => "Women's Wear", 'price' => 159.90, 'cost' => 50.00, 'stock' => 30],
                ['name' => 'Evening Gown', 'cat' => "Women's Wear", 'price' => 399.90, 'cost' => 150.00, 'stock' => 15],
                ['name' => 'Leather Handbag', 'cat' => 'Accessories', 'price' => 299.90, 'cost' => 100.00, 'stock' => 20],
                ['name' => 'Canvas Sneakers', 'cat' => 'Footwear', 'price' => 99.90, 'cost' => 35.00, 'stock' => 45],
            ],
            'Green Mart' => [
                ['name' => 'Organic Avocados (Pack)', 'cat' => 'Fresh Produce', 'price' => 18.90, 'cost' => 10.00, 'stock' => 80],
                ['name' => 'Farm Fresh Milk 1L', 'cat' => 'Dairy & Eggs', 'price' => 8.50, 'cost' => 5.50, 'stock' => 200],
                ['name' => 'Free Range Eggs (12pcs)', 'cat' => 'Dairy & Eggs', 'price' => 12.90, 'cost' => 8.00, 'stock' => 150],
                ['name' => 'Potato Chips', 'cat' => 'Snacks & Beverages', 'price' => 4.50, 'cost' => 2.00, 'stock' => 300],
                ['name' => 'Mineral Water 1.5L', 'cat' => 'Snacks & Beverages', 'price' => 2.50, 'cost' => 1.00, 'stock' => 500],
                ['name' => 'Laundry Detergent 3kg', 'cat' => 'Household Essentials', 'price' => 25.90, 'cost' => 15.00, 'stock' => 60],
            ],
            'Tech Gadgets' => [
                ['name' => 'Smartphone X Pro', 'cat' => 'Smartphones', 'price' => 3999.00, 'cost' => 2800.00, 'stock' => 20],
                ['name' => 'Ultra Slim Laptop', 'cat' => 'Laptops', 'price' => 4599.00, 'cost' => 3500.00, 'stock' => 10],
                ['name' => 'Wireless Earbuds', 'cat' => 'Accessories & Peripherals', 'price' => 299.00, 'cost' => 150.00, 'stock' => 50],
                ['name' => 'Mechanical Keyboard', 'cat' => 'Accessories & Peripherals', 'price' => 349.00, 'cost' => 180.00, 'stock' => 30],
                ['name' => 'Smart Bulb Color', 'cat' => 'Smart Home', 'price' => 49.00, 'cost' => 20.00, 'stock' => 100],
                ['name' => 'Smart Plug WiFi', 'cat' => 'Smart Home', 'price' => 35.00, 'cost' => 15.00, 'stock' => 80],
            ],
            'City Bookstore' => [
                ['name' => 'The Great Novel', 'cat' => 'Fiction', 'price' => 45.90, 'cost' => 25.00, 'stock' => 60],
                ['name' => 'History of Time', 'cat' => 'Non-Fiction', 'price' => 55.90, 'cost' => 30.00, 'stock' => 40],
                ['name' => 'The Art of Thinking', 'cat' => 'Non-Fiction', 'price' => 49.90, 'cost' => 28.00, 'stock' => 35],
                ['name' => 'Learning ABCs', 'cat' => "Children's Books", 'price' => 15.90, 'cost' => 8.00, 'stock' => 80],
                ['name' => 'Fun with Numbers', 'cat' => "Children's Books", 'price' => 18.90, 'cost' => 10.00, 'stock' => 70],
                ['name' => 'Premium Notebook', 'cat' => 'Stationery', 'price' => 25.90, 'cost' => 10.00, 'stock' => 100],
                ['name' => 'Gel Pen Set (12pcs)', 'cat' => 'Stationery', 'price' => 9.90, 'cost' => 4.00, 'stock' => 120],
            ],
        ];

        // Build outlet name → model lookup
        $outletByName = $outlets->keyBy('name');

        foreach ($outletProducts as $outletName => $items) {
            $outlet = $outletByName->get($outletName);

            if (!$outlet) {
                $this->command->warn("Outlet not found: {$outletName} — skipping its products");
                continue;
            }

            foreach ($items as $item) {
                $category = $categories->where('name', $item['cat'])->first();
                if (!$category) {
                    $this->command->warn("Category not found: {$item['cat']} — skipping {$item['name']}");
                    continue;
                }

                // Create the global product (unique by name)
                $product = Product::firstOrCreate(
                    ['name' => $item['name']],
                    [
                        'category_id' => $category->id,
                        'slug' => Str::slug($item['name']) . '-' . strtolower(Str::random(4)),
                        'sku' => strtoupper(Str::random(3) . '-' . Str::random(5)),
                        'barcode' => strtoupper(Str::random(12)),
                        'description' => 'Premium quality ' . strtolower($item['name']),
                        'price' => $item['price'],
                        'cost' => $item['cost'],
                        'stock_level' => $item['stock'],
                        'is_active' => true,
                        'has_variants' => false,
                    ]
                );

                // Assign ONLY to this outlet
                $product->prices()->updateOrCreate(
                    ['outlet_id' => $outlet->id],
                    [
                        'price' => $item['price'],
                        'stock_level' => $item['stock'],
                    ]
                );
            }

            $this->command->info("✓ {$outletName}: " . count($items) . ' products');
        }
    }
}
