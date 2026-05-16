<?php

namespace App\Filament\Widgets\Subscription;

use App\Models\Toko;
use App\Services\SubscriptionService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class SubscriptionStatusWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.subscription-status';

    public static function canView(): bool
    {
        return false;
    }

    /**
     * @return array<int, array{label: string, value: string, description: string, icon: string, color: string}>
     */
    public function getStats(): array
    {
        $user = Auth::user();

        if (! $user || ! filled($user->toko_id)) {
            return [];
        }

        $toko = Toko::with('subscription')->find($user->toko_id);

        if (! $toko) {
            return [];
        }

        $subscription = $toko->subscription;
        $service = app(SubscriptionService::class);

        $planLabel = $subscription
            ? ($subscription->plan?->getLabel() ?? $subscription->name)
            : 'Tidak ada langganan';

        $planColor = $subscription
            ? ($subscription->plan?->getColor() ?? 'gray')
            : 'danger';

        $stats = [
            [
                'label' => 'Paket Aktif',
                'value' => $planLabel,
                'description' => $subscription ? $subscription->name : 'Belum ada paket aktif — pilih paket untuk memulai',
                'icon' => 'heroicon-m-credit-card',
                'color' => $planColor,
            ],
        ];

        $stats[] = $this->usageStat('Produk', $toko->products()->count(), $subscription?->getLimit('max_products'), 'heroicon-m-cube');
        $stats[] = $this->usageStat('Kategori', $toko->categories()->count(), $subscription?->getLimit('max_categories'), 'heroicon-m-tag');
        $stats[] = $this->usageStat('Staff', $toko->users()->whereIn('role', ['kasir', 'gudang'])->count(), $subscription?->getLimit('max_staff'), 'heroicon-m-users');
        $stats[] = $this->usageStat('Metode Pembayaran', $toko->paymentMethods()->count(), $subscription?->getLimit('max_payment_methods'), 'heroicon-m-banknotes');

        $stats[] = $this->featureStat('Inventori', $service->canUseInventory($toko), 'heroicon-m-archive-box', 'Pro');
        $stats[] = $this->featureStat('Varian Produk', $service->canUseVariants($toko), 'heroicon-m-adjustments-horizontal', 'Pro');
        $stats[] = $this->featureStat('Diskon Produk', $service->canUseDiscounts($toko), 'heroicon-m-receipt-percent', 'Pro');
        $stats[] = $this->featureStat('Ekspor Laporan', $service->canExportReports($toko), 'heroicon-m-document-arrow-down', 'Pro');

        return $stats;
    }

    /** @return array<string, mixed> */
    private function usageStat(string $label, int $used, ?int $max, string $icon): array
    {
        if ($max === null) {
            return ['label' => $label, 'value' => (string) $used, 'description' => 'Tidak terbatas', 'icon' => $icon, 'color' => 'success'];
        }

        $pct = $max > 0 ? ($used / $max) * 100 : 100;
        $color = $pct >= 100 ? 'danger' : ($pct >= 75 ? 'warning' : 'success');
        $description = $pct >= 100 ? 'Batas tercapai — upgrade ke Pro untuk menambah' : "{$used} / {$max} digunakan";

        return ['label' => $label, 'value' => "{$used} / {$max}", 'description' => $description, 'icon' => $icon, 'color' => $color];
    }

    /** @return array<string, mixed> */
    private function featureStat(string $label, bool $enabled, string $icon, string $requiredPlan): array
    {
        if ($enabled) {
            return ['label' => $label, 'value' => 'Aktif', 'description' => 'Fitur tersedia', 'icon' => $icon, 'color' => 'success'];
        }

        return ['label' => $label, 'value' => 'Terkunci 🔒', 'description' => "Upgrade ke paket {$requiredPlan}", 'icon' => $icon, 'color' => 'gray'];
    }
}
