<?php

use App\Enums\SubscriptionPlan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $free = $this->planPayload(SubscriptionPlan::Free);
        $medium = $this->planPayload(SubscriptionPlan::Medium);
        $premium = $this->planPayload(SubscriptionPlan::Premium);

        DB::table('subscriptions')
            ->where('plan', 'pro')
            ->update(array_merge($premium, ['updated_at' => now()]));

        DB::table('subscriptions')
            ->where('plan', $free['plan'])
            ->update(array_merge($free, ['updated_at' => now()]));

        DB::table('subscriptions')
            ->where('plan', $premium['plan'])
            ->update(array_merge($premium, ['updated_at' => now()]));

        if (! DB::table('subscriptions')->where('plan', $medium['plan'])->exists()) {
            DB::table('subscriptions')->insert(array_merge($medium, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $proLimits = [
            'max_products' => null,
            'max_categories' => null,
            'max_staff' => null,
            'max_payment_methods' => null,
            'can_export_reports' => true,
            'can_use_inventory' => true,
            'can_use_variants' => true,
            'can_use_discounts' => true,
        ];

        DB::table('subscriptions')
            ->where('plan', 'premium')
            ->update([
                'name' => 'Pro',
                'plan' => 'pro',
                'price' => 150000,
                'duration_months' => 1,
                'features' => json_encode([
                    'Produk Tidak Terbatas',
                    'Kategori Tidak Terbatas',
                    'Staff Tidak Terbatas',
                    'Metode Pembayaran Tidak Terbatas',
                    'Ekspor Laporan',
                    'Manajemen Inventori',
                    'Varian Produk',
                    'Diskon Produk',
                ]),
                'limits' => json_encode($proLimits),
                'updated_at' => now(),
            ]);

        DB::table('subscriptions')->where('plan', 'medium')->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function planPayload(SubscriptionPlan $plan): array
    {
        return [
            'name' => $plan->getLabel(),
            'plan' => $plan->value,
            'price' => $plan->price(),
            'duration_months' => $plan->durationMonths(),
            'features' => json_encode($plan->marketingFeatures()),
            'limits' => json_encode($plan->defaultLimits()),
            'is_active' => true,
        ];
    }
};
