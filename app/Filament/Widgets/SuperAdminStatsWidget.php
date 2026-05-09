<?php

namespace App\Filament\Widgets;

use App\Models\Cafe;
use App\Models\CafeManager;
use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SuperAdminStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->role === 'super_admin';
    }

    protected function getStats(): array
    {
        $totalCafes = Cafe::query()->count();
        $activeCafes = Cafe::query()->where('is_active', true)->count();
        $inactiveCafes = $totalCafes - $activeCafes;

        $subscribedCafes = Cafe::query()->whereNotNull('subscription_id')->count();
        $unsubscribed = $totalCafes - $subscribedCafes;

        $totalManagers = CafeManager::query()->count();
        $unassigned = Cafe::query()
            ->whereDoesntHave('manager')
            ->where('is_active', true)
            ->count();

        $activePlans = Subscription::query()->where('is_active', true)->count();

        return [
            Stat::make('Total Klien Cafe', $totalCafes)
                ->description($activeCafes.' aktif · '.$inactiveCafes.' nonaktif')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('amber'),

            Stat::make('Berlangganan', $subscribedCafes)
                ->description($unsubscribed.' cafe belum berlangganan')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color($unsubscribed > 0 ? 'warning' : 'success'),

            Stat::make('Manager Ditugaskan', $totalManagers)
                ->description($unassigned.' cafe belum ada manager')
                ->descriptionIcon('heroicon-m-user-group')
                ->color($unassigned > 0 ? 'warning' : 'success'),

            Stat::make('Plan Tersedia', $activePlans)
                ->description('subscription plan aktif')
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),
        ];
    }
}
