<?php

namespace App\Filament\Pages\Owner;

use App\Filament\Widgets\Owner\OwnerDailyRevenueChart;
use App\Filament\Widgets\Owner\OwnerLowStockTable;
use App\Filament\Widgets\Owner\OwnerStaffPerformanceTable;
use App\Filament\Widgets\Owner\OwnerStatsWidget;
use App\Filament\Widgets\Owner\OwnerTokoTransactionsTable;
use App\Filament\Widgets\Owner\OwnerTopProductsChart;
use App\Filament\Widgets\Subscription\SubscriptionUpgradeWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class OwnerPanelDashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard Owner';

    protected static string $routePath = '/';

    public static function canAccess(): bool
    {
        $role = Auth::user()?->role;

        return Auth::check() && in_array($role, ['owner', 'manager'], true);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getWidgets(): array
    {
        return [
            SubscriptionUpgradeWidget::class,
            OwnerStatsWidget::class,
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
