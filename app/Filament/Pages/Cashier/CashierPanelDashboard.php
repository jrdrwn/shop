<?php

namespace App\Filament\Pages\Cashier;

use App\Filament\Widgets\Cashier\CashierHourlyTransactionsChart;
use App\Filament\Widgets\Cashier\CashierStatsWidget;
use App\Filament\Widgets\Cashier\CashierTodayTransactionsTable;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class CashierPanelDashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard Kasir';

    protected static string $routePath = '/';

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
