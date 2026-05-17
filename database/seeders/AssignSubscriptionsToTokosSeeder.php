<?php

namespace Database\Seeders;

use App\Enums\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Toko;
use Illuminate\Database\Seeder;

class AssignSubscriptionsToTokosSeeder extends Seeder
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
            Toko::whereSubscriptionId(null)->update(['subscription_id' => $free->id]);
        }
    }
}
