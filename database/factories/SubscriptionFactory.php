<?php

namespace Database\Factories;

use App\Enums\SubscriptionPlan;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        $plan = fake()->randomElement(SubscriptionPlan::cases());

        return [
            'name' => $plan->getLabel().' '.fake()->randomElement(['Bulanan', 'Tahunan']),
            'plan' => $plan,
            'price' => $plan->price(),
            'duration_months' => $plan->durationMonths(),
            'features' => $plan->marketingFeatures(),
            'limits' => null, // use plan defaults
            'is_active' => true,
        ];
    }

    public function free(): static
    {
        return $this->state([
            'plan' => SubscriptionPlan::Free,
            'price' => SubscriptionPlan::Free->price(),
            'duration_months' => SubscriptionPlan::Free->durationMonths(),
            'features' => SubscriptionPlan::Free->marketingFeatures(),
            'limits' => null,
        ]);
    }

    public function pro(): static
    {
        return $this->state([
            'plan' => SubscriptionPlan::Pro,
            'price' => SubscriptionPlan::Pro->price(),
            'duration_months' => SubscriptionPlan::Pro->durationMonths(),
            'features' => SubscriptionPlan::Pro->marketingFeatures(),
            'limits' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
