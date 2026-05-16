<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Warehouse\WarehouseDashboard;
use App\Http\Middleware\AuthenticateWarehouse;
use App\Http\Middleware\CheckSubscriptionExpiry;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class WarehousePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('warehouse')
            ->path('warehouse')
            ->viteTheme('resources/css/filament/warehouse/theme.css')
            ->brandName('Gudang Toko')
            ->brandLogo(asset('/default-logo/light-mode.png'))
            ->darkModeBrandLogo(asset('/default-logo/dark-mode.png'))
            ->brandLogoHeight('2rem')
            ->topNavigation()
            ->login()
            ->colors([
                'primary' => Color::Fuchsia,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages/Warehouse'), for: 'App\Filament\Pages\Warehouse')
            ->pages([
                WarehouseDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets/Warehouse'), for: 'App\Filament\Widgets\Warehouse')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                AuthenticateWarehouse::class,
                CheckSubscriptionExpiry::class,
            ])
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn(): string => Blade::render('<x-warehouse-role-links />')
            );
    }
}
