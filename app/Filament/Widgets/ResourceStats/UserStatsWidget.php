<?php

namespace App\Filament\Widgets\ResourceStats;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class UserStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $cafeId = $user?->cafe_id;

        if (! filled($cafeId)) {
            return [];
        }

        $baseQuery = User::query()
            ->where('cafe_id', $cafeId)
            ->where('role', 'cashier');

        return [
            Stat::make('Total Kasir', (clone $baseQuery)->count())
                ->description('semua kasir')
                ->color('primary'),
            Stat::make('Kasir Aktif', (clone $baseQuery)->where('is_active', true)->count())
                ->description('bisa login')
                ->color('success'),
            Stat::make('Kasir Non-aktif', (clone $baseQuery)->where('is_active', false)->count())
                ->description('akses dicabut')
                ->color('danger'),
        ];
    }
}
