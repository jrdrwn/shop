<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Filament\Widgets\OwnerDailyRevenueChart;
use App\Filament\Widgets\OwnerLowStockTable;
use App\Filament\Widgets\OwnerStaffPerformanceTable;
use App\Filament\Widgets\OwnerStatsWidget;
use App\Filament\Widgets\OwnerTokoTransactionsTable;
use App\Filament\Widgets\OwnerTopProductsChart;
use App\Filament\Widgets\SubscriptionStatusWidget;
use App\Filament\Widgets\SubscriptionUpgradeWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class OwnerDashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard Owner';

    protected static string $routePath = 'owner';

    public static function canAccess(): bool
    {
        return Auth::user()?->role === UserRole::Owner->value || Auth::user()?->role === 'owner';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getWidgets(): array
    {
        return [
            SubscriptionUpgradeWidget::class,
            SubscriptionStatusWidget::class,
            OwnerStatsWidget::class,
            \App\Filament\Widgets\ResourceStats\CashFlowStatsWidget::class,
            OwnerDailyRevenueChart::class,
            OwnerTopProductsChart::class,
            OwnerLowStockTable::class,
            OwnerStaffPerformanceTable::class,
            OwnerTokoTransactionsTable::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
