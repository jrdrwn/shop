<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class OwnerDailyRevenueChart extends ChartWidget
{
    protected ?string $heading = 'Tren Pendapatan 14 Hari';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return Auth::user()?->role === UserRole::Owner->value || Auth::user()?->role === 'owner';
    }

    protected function getData(): array
    {
        $tokoId = Auth::user()?->toko_id;

        $data = Transaction::query()
            ->where('toko_id', $tokoId)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(14))
            ->where('created_at', '<=', now())
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('total', 'date');

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $data->values()->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
