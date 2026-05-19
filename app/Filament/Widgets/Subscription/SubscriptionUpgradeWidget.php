<?php

namespace App\Filament\Widgets\Subscription;

use App\Enums\SubscriptionPlan;
use App\Enums\UserRole;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Toko;
use App\Services\MidtransService;
use App\Services\SubscriptionService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionUpgradeWidget extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?string $snapToken = null;

    public ?string $clientKey = null;

    public ?string $snapUrl = null;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.subscription-upgrade';

    public static function canView(): bool
    {
        return Auth::user()?->role === UserRole::Owner->value || Auth::user()?->role === 'owner';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPlans(): array
    {
        $plans = [];

        foreach (SubscriptionPlan::cases() as $plan) {
            $subscription = Subscription::where('plan', $plan->value)->where('is_active', true)->first();

            if (! $subscription) {
                $subscription = new Subscription([
                    'plan' => $plan,
                    'name' => $plan->getLabel(),
                    'price' => $plan->price(),
                    'duration_months' => $plan->durationMonths(),
                    'features' => $plan->marketingFeatures(),
                    'is_active' => true,
                ]);
            }

            $plans[] = [
                'id' => $subscription->id,
                'plan' => $plan,
                'name' => $subscription->name,
                'price' => (int) $subscription->price,
                'duration_months' => (int) $subscription->duration_months,
                'features' => $subscription->features ?? $plan->marketingFeatures(),
                'limits' => $plan->defaultLimits(),
                'color' => $plan->getColor(),
            ];
        }

        return $plans;
    }

    public function getCurrentPlan(): ?array
    {
        $user = Auth::user();

        if (! $user || ! filled($user->toko_id)) {
            return null;
        }

        $toko = Toko::with('subscription')->find($user->toko_id);

        if (! $toko || ! $toko->subscription) {
            return null;
        }

        $subscription = $toko->subscription;
        $plan = $subscription->plan;

        // Auto downgrade check
        $expirySeconds = $this->getExpirySeconds();
        if ($expirySeconds !== null && $expirySeconds <= 0) {
            Log::info('Expiry reached, handling reset', ['expiry_seconds' => $expirySeconds]);

            $freePlan = Subscription::where('plan', 'free')->first();

            if ($freePlan && $toko->subscription_id !== $freePlan->id) {
                $toko->update(['subscription_id' => $freePlan->id]);
                Log::info('Toko downgraded to Free');
                $toko->refresh();
                $subscription = $toko->subscription;
                $plan = $subscription->plan;
            }
        }

        return [
            'name' => $plan?->getLabel() ?? $subscription->name,
            'color' => $plan?->getColor() ?? 'gray',
            'expiry_seconds' => $this->getExpirySeconds(),
        ];
    }

    public function getExpirySeconds(): ?int
    {
        $user = Auth::user();
        if (! $user || ! $user->toko_id) {
            return null;
        }

        $toko = Toko::with('subscription')->find($user->toko_id);

        // Don't show countdown for Free plan
        if ($toko && $toko->subscription && $toko->subscription->plan?->value === 'free') {
            return null;
        }

        $lastPayment = SubscriptionPayment::where('toko_id', $user->toko_id)
            ->where('status', 'success')
            ->latest()
            ->first();

        if (! $lastPayment || ! $lastPayment->subscription || ! $lastPayment->subscription->duration_months) {
            return null;
        }

        $startTime = $lastPayment->settlement_time ?? $lastPayment->created_at;
        $expiry = Carbon::parse($startTime)->addMonths($lastPayment->subscription->duration_months);

        if (now()->gt($expiry)) {
            $seconds = 0;
        } else {
            $seconds = now()->diffInSeconds($expiry);
        }

        return $seconds;
    }

    public function getStatusStats(): array
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

        $stats = [];
        $stats[] = $this->usageStat('Produk', $toko->products()->count(), $subscription?->getLimit('max_products'), 'heroicon-m-cube');
        $stats[] = $this->usageStat('Kategori', $toko->categories()->count(), $subscription?->getLimit('max_categories'), 'heroicon-m-tag');
        $stats[] = $this->usageStat('Staff', $toko->users()->whereIn('role', ['kasir', 'gudang'])->count(), $subscription?->getLimit('max_staff'), 'heroicon-m-users');
        $stats[] = $this->usageStat('Metode Pembayaran', $toko->paymentMethods()->count(), $subscription?->getLimit('max_payment_methods'), 'heroicon-m-banknotes');

        $stats[] = $this->featureStat('Inventori', $service->canUseInventory($toko), 'heroicon-m-archive-box', 'Medium');
        $stats[] = $this->featureStat('Varian Produk', $service->canUseVariants($toko), 'heroicon-m-adjustments-horizontal', 'Medium');
        $stats[] = $this->featureStat('Diskon Produk', $service->canUseDiscounts($toko), 'heroicon-m-receipt-percent', 'Medium');
        $stats[] = $this->featureStat('Ekspor Laporan', $service->canExportReports($toko), 'heroicon-m-document-arrow-down', 'Medium');

        return $stats;
    }

    private function usageStat(string $label, int $used, ?int $max, string $icon): array
    {
        if ($max === null) {
            return ['label' => $label, 'value' => (string) $used, 'description' => 'Tidak terbatas', 'icon' => $icon, 'color' => 'success'];
        }
        $pct = $max > 0 ? ($used / $max) * 100 : 100;
        $color = $pct >= 100 ? 'danger' : ($pct >= 75 ? 'warning' : 'success');
        $description = $pct >= 100 ? 'Batas tercapai' : "{$used} / {$max} digunakan";

        return ['label' => $label, 'value' => "{$used} / {$max}", 'description' => $description, 'icon' => $icon, 'color' => $color];
    }

    private function featureStat(string $label, bool $enabled, string $icon, string $requiredPlan): array
    {
        if ($enabled) {
            return ['label' => $label, 'value' => 'Aktif', 'description' => 'Fitur tersedia', 'icon' => $icon, 'color' => 'success'];
        }

        return ['label' => $label, 'value' => 'Terkunci 🔒', 'description' => "Upgrade ke paket {$requiredPlan}", 'icon' => $icon, 'color' => 'gray'];
    }

    /**
     * @return array<Action>
     */
    protected function getActions(): array
    {
        return [
            $this->selectPlanAction(),
        ];
    }

    public function selectPlanAction(): Action
    {
        return Action::make('selectPlan')
            ->label('Pilih Paket')
            ->modalHeading('Pilih Paket Langganan')
            ->modalDescription('Pilih paket yang sesuai untuk toko Anda.')
            ->modalSubmitActionLabel('Lanjutkan ke Pembayaran')
            ->form([
                Select::make('subscription_id')
                    ->label('Paket')
                    ->options(function (): array {
                        $user = Auth::user();
                        $currentToko = $user?->toko;
                        $currentPlan = $currentToko?->subscription?->plan;

                        $options = [];
                        foreach (SubscriptionPlan::cases() as $plan) {
                            $subscription = Subscription::where('plan', $plan->value)->where('is_active', true)->first();

                            if ($subscription) {
                                $label = "{$subscription->name} — Rp ".number_format((int) $subscription->price, 0, ',', '.');
                                $isDisabled = false;

                                // Disable current plan
                                if ($currentPlan && $currentPlan->value === $plan->value) {
                                    $label .= ' (Paket Aktif)';
                                    $isDisabled = true;
                                }

                                // Disable downgrades via UI
                                if ($currentPlan && $currentPlan->price() > $plan->price()) {
                                    $isDisabled = true;
                                }

                                if (! $isDisabled) {
                                    $options[$subscription->id] = $label;
                                }
                            }
                        }

                        return $options;
                    })
                    ->required(),
            ])
            ->action(function (array $data): void {
                $user = Auth::user();

                if (! $user || ! $user->toko_id) {
                    Notification::make()
                        ->title('Gagal')
                        ->body('Toko tidak ditemukan.')
                        ->danger()
                        ->send();

                    return;
                }

                $toko = Toko::find($user->toko_id);
                $subscription = Subscription::findOrFail($data['subscription_id']);

                if ($subscription->price <= 0) {
                    app(SubscriptionService::class)->activateSubscription($toko, $subscription, 'free-plan');

                    Notification::make()
                        ->title('Berhasil')
                        ->body('Paket Free telah diaktifkan.')
                        ->success()
                        ->send();

                    return;
                }

                try {
                    $this->snapToken = app(SubscriptionService::class)->initiateUpgrade($toko, $subscription);
                    $midtrans = app(MidtransService::class);
                    $this->clientKey = $midtrans->clientKey();
                    $this->snapUrl = $midtrans->snapUrl();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('Gagal memulai pembayaran')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
