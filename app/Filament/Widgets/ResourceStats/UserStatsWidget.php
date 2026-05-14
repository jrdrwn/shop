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
        $tokoId = $user?->toko_id;

        if (! filled($tokoId)) {
            return [];
        }

        $baseQuery = User::query()
            ->where('toko_id', $tokoId)
            ->whereIn('role', ['kasir', 'gudang']);

        return [
            Stat::make('Total Pengguna', (clone $baseQuery)->count())
                ->description('semua staf (kasir & gudang)')
                ->color('primary'),
            Stat::make('Total Kasir', (clone $baseQuery)->where('role', 'kasir')->count())
                ->description('staf operasional kasir')
                ->color('success'),
            Stat::make('Total Gudang', (clone $baseQuery)->where('role', 'gudang')->count())
                ->description('staf manajemen gudang')
                ->color('warning'),
        ];
    }
}
