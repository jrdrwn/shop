<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use App\Models\Toko;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'toko_id' => Toko::factory(),
            'name' => fake()->randomElement(['Cash', 'Transfer BCA', 'QRIS', 'GoPay', 'OVO', 'Dana']),
            'type' => fake()->randomElement(['cash', 'transfer', 'ewallet', 'qris']),
            'is_active' => true,
        ];
    }
}
