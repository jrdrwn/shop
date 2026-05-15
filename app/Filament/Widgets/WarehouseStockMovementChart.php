<?php

namespace App\Filament\Widgets;

use App\Models\InventoryLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class WarehouseStockMovementChart extends ChartWidget
{
    protected ?string $heading = 'Tren Pergerakan Stok (7 Hari Terakhir)';
    
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $tokoId = Auth::user()?->toko_id;
        
        // Data Stok Masuk
        $inData = InventoryLog::query()
            ->where('toko_id', $tokoId)
            ->where('quantity_change', '>', 0)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(quantity_change) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date');

        // Data Stok Keluar (abs untuk nilai positif di chart)
        $outData = InventoryLog::query()
            ->where('toko_id', $tokoId)
            ->where('quantity_change', '<', 0)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(ABS(quantity_change)) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date');

        $labels = [];
        $inValues = [];
        $outValues = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->isoFormat('D MMM');
            $inValues[] = $inData[$date] ?? 0;
            $outValues[] = $outData[$date] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Stok Masuk',
                    'data' => $inValues,
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Stok Keluar',
                    'data' => $outValues,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
