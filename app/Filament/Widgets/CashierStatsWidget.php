<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CashierStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->role === 'cashier';
    }

    protected function getStats(): array
    {
        $userId = Auth::id();

        $todayAll = Transaction::query()
            ->where('cashier_id', $userId)
            ->whereDate('created_at', today());

        $pendingCount = (clone $todayAll)->where('status', 'pending')->count();
        $completedRevenue = (int) (clone $todayAll)->where('status', 'completed')->sum('total_amount');

        return [
            Stat::make('Transaksi Hari Ini', $todayAll->count())
                ->description('semua yang diproses shift ini')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('primary'),
            Stat::make('Omzet Saya Hari Ini', 'Rp '.number_format($completedRevenue, 0, ',', '.'))
                ->description('hanya transaksi completed')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Transaksi Pending', $pendingCount)
                ->description('menunggu proses')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'gray'),
        ];
    }
}
