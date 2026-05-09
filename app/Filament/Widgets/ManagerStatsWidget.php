<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ManagerStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->role === 'manager';
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $cafeId = $user?->cafe_id;

        if (! filled($cafeId)) {
            return [];
        }

        $todayQuery = Transaction::query()
            ->where('cafe_id', $cafeId)
            ->whereDate('created_at', today());

        $thisMonth = (int) Transaction::query()
            ->where('cafe_id', $cafeId)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('total_amount');

        $lastMonth = (int) Transaction::query()
            ->where('cafe_id', $cafeId)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->where('created_at', '<', now()->startOfMonth())
            ->sum('total_amount');

        $growth = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;

        return [
            Stat::make('Produk Aktif', Product::query()->where('cafe_id', $cafeId)->where('is_active', true)->count())
                ->description('siap dijual')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            Stat::make('Transaksi Hari Ini', $todayQuery->count())
                ->description('semua status')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('amber'),
            Stat::make('Omzet Hari Ini', 'Rp '.number_format((int) (clone $todayQuery)->where('status', 'completed')->sum('total_amount'), 0, ',', '.'))
                ->description('hanya completed')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Omzet Bulan Ini', 'Rp '.number_format($thisMonth, 0, ',', '.'))
                ->description(($growth >= 0 ? '+' : '').$growth.'% vs bulan lalu')
                ->descriptionIcon($growth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growth >= 0 ? 'success' : 'danger'),
        ];
    }
}
