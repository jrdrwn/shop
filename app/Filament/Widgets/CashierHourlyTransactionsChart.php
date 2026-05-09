<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class CashierHourlyTransactionsChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Transaksi Per Jam';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return Auth::user()?->role === 'cashier';
    }

    protected function getData(): array
    {
        $transactions = Transaction::query()
            ->where('cashier_id', Auth::id())
            ->whereDate('created_at', today())
            ->get(['created_at']);

        $data = $transactions
            ->groupBy(static fn (Transaction $t): int => (int) $t->created_at->format('G'))
            ->map(static fn ($group): int => $group->count());

        $allHours = collect(range(0, 23))
            ->mapWithKeys(fn ($hour) => [$hour => (int) $data->get($hour, 0)]);

        return [
            'datasets' => [
                [
                    'label' => 'Transaksi',
                    'data' => $allHours->values()->toArray(),
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.15)',
                    'fill' => true,
                ],
            ],
            'labels' => $allHours->keys()->map(fn ($h) => sprintf('%02d:00', $h))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
