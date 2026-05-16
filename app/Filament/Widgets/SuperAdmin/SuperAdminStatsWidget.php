<?php

namespace App\Filament\Widgets\SuperAdmin;

use App\Models\Subscription;
use App\Models\Toko;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SuperAdminStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 3;

    public static function canView(): bool
    {
        return Auth::user()?->role === 'super_admin';
    }

    protected function getStats(): array
    {
        $totalTokos = Toko::query()->count();
        $activeTokos = Toko::query()->where('is_active', true)->count();
        $inactiveTokos = $totalTokos - $activeTokos;

        $activePlans = Subscription::query()->where('is_active', true)->count();

        $totalTransactions = Transaction::count();
        $totalStaff = User::whereIn('role', ['owner', 'kasir', 'gudang'])->count();

        return [
            Stat::make('Total Klien Toko', $totalTokos)
                ->description($activeTokos.' aktif · '.$inactiveTokos.' nonaktif')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('amber'),

            Stat::make('Total Transaksi', $totalTransactions)
                ->description('Semua transaksi tercatat')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),

            Stat::make('Total Staff', $totalStaff)
                ->description('Owner & Kasir terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Plan Tersedia', $activePlans)
                ->description('subscription plan aktif')
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),
        ];
    }
}
