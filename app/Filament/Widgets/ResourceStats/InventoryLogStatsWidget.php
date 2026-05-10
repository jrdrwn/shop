<?php

namespace App\Filament\Widgets\ResourceStats;

use App\Models\InventoryLog;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class InventoryLogStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $cafeId = $user?->cafe_id;

        if (! filled($cafeId)) {
            return [];
        }

        $baseQuery = InventoryLog::query()
            ->where('cafe_id', $cafeId)
            ->whereDate('created_at', today());

        return [
            Stat::make('Log Hari Ini', (clone $baseQuery)->count())
                ->description('total aktivitas')
                ->color('primary'),
            Stat::make('Stok Masuk', (clone $baseQuery)->where('quantity_change', '>', 0)->count())
                ->description('penambahan stok')
                ->color('success'),
            Stat::make('Stok Keluar', (clone $baseQuery)->where('quantity_change', '<', 0)->count())
                ->description('pengurangan stok')
                ->color('danger'),
        ];
    }
}
