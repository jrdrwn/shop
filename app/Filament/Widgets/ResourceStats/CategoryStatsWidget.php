<?php

namespace App\Filament\Widgets\ResourceStats;

use App\Models\Category;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CategoryStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $tokoId = $user?->toko_id;

        if (! filled($tokoId)) {
            return [];
        }

        $baseQuery = Category::query()->where('toko_id', $tokoId);

        return [
            Stat::make('Total Kategori', (clone $baseQuery)->count())
                ->description('semua kategori')
                ->color('primary'),
            Stat::make('Kategori Aktif', (clone $baseQuery)->where('is_active', true)->count())
                ->description('bisa digunakan')
                ->color('success'),
            Stat::make('Kategori Non-aktif', (clone $baseQuery)->where('is_active', false)->count())
                ->description('tidak muncul di POS')
                ->color('danger'),
        ];
    }
}
