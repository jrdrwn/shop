<?php

namespace Database\Factories;

use App\Models\Cafe;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cafe_id' => Cafe::factory(),
            'name' => fake()->randomElement(['Cash', 'Transfer BCA', 'QRIS', 'GoPay', 'OVO', 'Dana']),
            'type' => fake()->randomElement(['cash', 'transfer', 'ewallet', 'qris']),
            'is_active' => true,
        ];
    }
}
