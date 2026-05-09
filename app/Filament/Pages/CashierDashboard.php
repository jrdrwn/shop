<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CashierHourlyTransactionsChart;
use App\Filament\Widgets\CashierStatsWidget;
use App\Filament\Widgets\CashierTodayTransactionsTable;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class CashierDashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard Kasir';

    protected static string $routePath = 'cashier';

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'cashier';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getWidgets(): array
    {
        return [
            CashierStatsWidget::class,
            CashierHourlyTransactionsChart::class,
            CashierTodayTransactionsTable::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
