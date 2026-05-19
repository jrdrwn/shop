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

    public function medium(): static
    {
        return $this->state([
            'plan' => SubscriptionPlan::Medium,
            'price' => SubscriptionPlan::Medium->price(),
            'duration_months' => SubscriptionPlan::Medium->durationMonths(),
            'features' => SubscriptionPlan::Medium->marketingFeatures(),
            'limits' => null,
        ]);
    }

    public function premium(): static
    {
        return $this->state([
            'plan' => SubscriptionPlan::Premium,
            'price' => SubscriptionPlan::Premium->price(),
            'duration_months' => SubscriptionPlan::Premium->durationMonths(),
            'features' => SubscriptionPlan::Premium->marketingFeatures(),
            'limits' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
