<?php

namespace App\Filament\Pages\Manager;

use App\Filament\Widgets\ManagerCafeTransactionsTable;
use App\Filament\Widgets\ManagerDailyRevenueChart;
use App\Filament\Widgets\ManagerLowStockTable;
use App\Filament\Widgets\ManagerStaffPerformanceTable;
use App\Filament\Widgets\ManagerStatsWidget;
use App\Filament\Widgets\ManagerTopProductsChart;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class ManagerPanelDashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard Manager';

    protected static string $routePath = '/';

    public function getWidgets(): array
    {
        return [
            ManagerStatsWidget::class,
            ManagerDailyRevenueChart::class,
            ManagerTopProductsChart::class,
            ManagerLowStockTable::class,
            ManagerStaffPerformanceTable::class,
            ManagerCafeTransactionsTable::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
