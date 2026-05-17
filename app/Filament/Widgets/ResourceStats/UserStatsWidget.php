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

        $baseQuery = User::query()->where('toko_id', $tokoId)->whereNot('role', 'owner');

        return [
            Stat::make('Total Pengguna', (clone $baseQuery)->count())
                ->description('semua pengguna')
                ->color('primary'),
            Stat::make('Pengguna Aktif', (clone $baseQuery)->where('is_active', true)->count())
                ->description('siap digunakan')
                ->color('success'),
            Stat::make('Pengguna Nonaktif', (clone $baseQuery)->where('is_active', false)->count())
                ->description('tidak dapat login')
                ->color('danger'),
        ];
    }
}
