<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Features\SupportRedirects\Redirector;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register custom registration response untuk redirect ke panel yang benar
        $this->app->bind(RegistrationResponse::class, function ($app) {
            return new class implements RegistrationResponse
            {
                public function toResponse($request): RedirectResponse|Redirector
                {
                    $panel = Filament::getCurrentPanel();

                    // Jika dari panel owner, redirect ke owner
                    if ($panel && $panel->getId() === 'owner') {
                        return redirect()->intended($panel->getUrl());
                    }

                    // Default: redirect ke panel saat ini atau default
                    return redirect()->intended(Filament::getUrl());
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
