<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ManagerCafeTransactionsTable;
use App\Filament\Widgets\ManagerDailyRevenueChart;
use App\Filament\Widgets\ManagerLowStockTable;
use App\Filament\Widgets\ManagerStaffPerformanceTable;
use App\Filament\Widgets\ManagerStatsWidget;
use App\Filament\Widgets\ManagerTopProductsChart;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class ManagerDashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard Manager';

    protected static string $routePath = 'manager';

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'manager';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

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
