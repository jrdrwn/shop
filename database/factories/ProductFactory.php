<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Toko;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'toko_id' => Toko::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(10000, 100000),
            'discount_percentage' => 0,
            'stock' => fake()->numberBetween(0, 100),
            'sku' => strtoupper(fake()->bothify('SKU-####')),
            'image_url' => null,
            'is_active' => true,
            'has_variants' => false,
            'variants' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
