<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Owner\OwnerPanelDashboard;
use App\Filament\Pages\Owner\OwnerRegistration;
use App\Filament\Pages\OwnerLogin;
use App\Http\Middleware\CheckSubscriptionExpiry;
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

class OwnerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('owner')
            ->path('owner')
            ->viteTheme('resources/css/filament/owner/theme.css')
            ->brandName('Owner Toko')
            ->brandLogo(asset('/default-logo/light-mode.png'))
            ->darkModeBrandLogo(asset('/default-logo/dark-mode.png'))
            ->brandLogoHeight('2rem')
            ->login(OwnerLogin::class)
            ->registration(OwnerRegistration::class)
            ->homeUrl('/owner')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->pages([
                OwnerPanelDashboard::class,
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
                CheckSubscriptionExpiry::class,
            ])
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => Blade::render('<x-owner-role-links />')
            )
            ->plugins([
                LightSwitchPlugin::make(),
            ]);
    }
}
