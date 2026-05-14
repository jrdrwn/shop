<?php

namespace App\Filament\Widgets\ResourceStats;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ProductStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $tokoId = $user?->toko_id;

        if (! filled($tokoId)) {
            return [];
        }

        $baseQuery = Product::query()->where('toko_id', $tokoId);

        return [
            Stat::make('Total Produk', (clone $baseQuery)->count())
                ->description('semua produk')
                ->color('primary'),
            Stat::make('Produk Aktif', (clone $baseQuery)->where('is_active', true)->count())
                ->description('siap dijual')
                ->color('success'),
            Stat::make('Stok Habis', (clone $baseQuery)->where('stock', '<=', 0)->count())
                ->description('perlu restock')
                ->color('danger'),
        ];
    }
}
