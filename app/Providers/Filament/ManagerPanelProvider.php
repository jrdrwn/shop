<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Manager\ManagerPanelDashboard;
use App\Filament\Pages\Manager\ManagerRegistration;
use App\Filament\Pages\ManagerLogin;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ManagerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('manager')
            ->path('manajer')
            ->viteTheme('resources/css/filament/manager/theme.css')
            ->brandName('Manager Cafe')
            ->brandLogo(asset('/default-logo/light-mode.png'))
            ->darkModeBrandLogo(asset('/default-logo/dark-mode.png'))
            ->brandLogoHeight("2rem")
            ->login(ManagerLogin::class)
            ->registration(ManagerRegistration::class)
            ->homeUrl('/manajer')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->pages([
                ManagerPanelDashboard::class,
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
                \App\Http\Middleware\CheckSubscriptionExpiry::class,
            ])
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn(): string => Blade::render('<x-manager-role-links />')
            )
            ->plugins([
                LightSwitchPlugin::make(),
            ]);
    }
}
