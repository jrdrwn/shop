<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TransactionStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $query = Transaction::query();

        if ($user?->role === 'manager' && filled($user->cafe_id)) {
            $query->where('cafe_id', $user->cafe_id);
        }

        if ($user?->role === 'cashier') {
            $query->where('cashier_id', $user->id);
        }

        $todayAmount = (clone $query)->whereDate('created_at', today())->where('status', 'completed')->sum('total_amount');
        $weekAmount = (clone $query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->where('status', 'completed')->sum('total_amount');
        $monthAmount = (clone $query)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where('status', 'completed')->sum('total_amount');

        return [
            Stat::make('Omzet Hari Ini', 'Rp '.number_format($todayAmount, 0, ',', '.'))
                ->description('Total penjualan sukses hari ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Omzet Minggu Ini', 'Rp '.number_format($weekAmount, 0, ',', '.'))
                ->description('Total penjualan sukses minggu ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make('Omzet Bulan Ini', 'Rp '.number_format($monthAmount, 0, ',', '.'))
                ->description('Total penjualan sukses bulan ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}
