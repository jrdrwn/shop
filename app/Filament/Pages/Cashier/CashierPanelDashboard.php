<?php

namespace App\Filament\Pages\Cashier;

use App\Filament\Widgets\Cashier\CashierHourlyTransactionsChart;
use App\Filament\Widgets\Cashier\CashierStatsWidget;
use App\Filament\Widgets\Cashier\CashierTodayTransactionsTable;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class CashierPanelDashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard Kasir';

    protected static string $routePath = '/';

    public static function canAccess(): bool
    {
        $role = Auth::user()?->role;

        return Auth::check() && in_array($role, ['kasir', 'cashier'], true);
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
