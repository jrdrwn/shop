<?php

namespace Database\Factories;

use App\Models\Cafe;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPayment>
 */
class SubscriptionPaymentFactory extends Factory
{
    protected $model = SubscriptionPayment::class;

    public function definition(): array
    {
        return [
            'cafe_id' => Cafe::factory(),
            'subscription_id' => Subscription::factory(),
            'order_id' => 'SUB-'.strtoupper(fake()->bothify('??###')),
            'amount' => fake()->randomElement([0, 150000]),
            'status' => fake()->randomElement(['pending', 'success', 'failed']),
            'payment_type' => null,
            'transaction_id' => null,
            'transaction_time' => null,
            'settlement_time' => null,
            'metadata' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function success(): static
    {
        return $this->state(['status' => 'success']);
    }

    public function failed(): static
    {
        return $this->state(['status' => 'failed']);
    }
}
