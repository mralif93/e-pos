<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => strtoupper(Str::random(10)),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'cost' => $this->faker->randomFloat(2, 5, 250),
            'stock_level' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
