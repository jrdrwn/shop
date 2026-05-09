<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SuperAdminCafeSummaryTable;
use App\Filament\Widgets\SuperAdminStatsWidget;
use App\Filament\Widgets\SuperAdminSubscriptionChart;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Platform Overview';

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'super_admin';
    }

    public function mount(): void
    {
        match (Auth::user()?->role) {
            'manager' => $this->redirect('/admin/manager', navigate: true),
            'cashier' => $this->redirect('/admin/cashier', navigate: true),
            default => null,
        };
    }

    public function getWidgets(): array
    {
        return [
            SuperAdminStatsWidget::class,
            SuperAdminSubscriptionChart::class,
            SuperAdminCafeSummaryTable::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
