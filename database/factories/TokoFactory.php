<?php

namespace Database\Factories;

use App\Models\Toko;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Toko>
 */
class TokoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Toko',
            'owner_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'address' => fake()->address(),
            'description' => fake()->sentence(),
            'logo_url' => null,
            'is_active' => true,
            'tax_percentage' => 0,
            'service_charge_percentage' => 0,
            'subscription_id' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
