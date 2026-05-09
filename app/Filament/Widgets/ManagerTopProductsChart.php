<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ManagerTopProductsChart extends ChartWidget
{
    protected ?string $heading = 'Produk Terlaris';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return Auth::user()?->role === 'manager';
    }

    protected function getData(): array
    {
        $cafeId = Auth::user()?->cafe_id;

        $topProducts = TransactionItem::query()
            ->whereHas('transaction', fn ($q) => $q->where('cafe_id', $cafeId)->where('status', 'completed'))
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
