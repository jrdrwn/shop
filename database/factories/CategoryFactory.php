<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'toko_id' => null,
            'name' => fake()->word(),
            'description' => null,
            'image_url' => null,
            'display_order' => 0,
            'is_active' => true,
        ];
    }
}
