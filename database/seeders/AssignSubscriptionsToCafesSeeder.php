<?php

namespace Database\Seeders;

use App\Enums\SubscriptionPlan;
use App\Models\Cafe;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class AssignSubscriptionsToCafesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (SubscriptionPlan::cases() as $plan) {
            Subscription::firstOrCreate(
                ['plan' => $plan->value],
                [
                    'name' => $plan->getLabel(),
                    'price' => $plan->price(),
                    'duration_months' => $plan->durationMonths(),
                    'features' => $plan->marketingFeatures(),
                    'is_active' => true,
                ]
            );
        }

        $free = Subscription::where('plan', SubscriptionPlan::Free->value)->first();

        if (! $free) {
            $free = Subscription::first('id');
        }

        if ($free) {
            Cafe::whereSubscriptionId(null)->update(['subscription_id' => $free->id]);
        }
    }
}
