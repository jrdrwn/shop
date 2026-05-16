<?php

namespace App\Filament\Widgets\Owner;

use App\Enums\UserRole;
use App\Models\TransactionItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class OwnerTopProductsChart extends ChartWidget
{
    protected ?string $heading = 'Produk Terlaris';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return Auth::user()?->role === UserRole::Owner->value || Auth::user()?->role === 'owner';
    }

    protected function getData(): array
    {
        $tokoId = Auth::user()?->toko_id;

        $topProducts = TransactionItem::query()
            ->whereHas('transaction', fn ($q) => $q->where('toko_id', $tokoId)->where('status', 'completed'))
            ->with('product')
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Terjual',
                    'data' => $topProducts->pluck('total_qty')->toArray(),
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $topProducts->pluck('product.name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
