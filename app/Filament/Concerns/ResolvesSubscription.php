<?php

namespace App\Filament\Concerns;

use App\Models\Cafe;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;

/**
 * Shared helpers for subscription-gated Filament resources and list pages.
 */
trait ResolvesSubscription
{
    /**
     * Resolve the cafe for the currently logged-in manager.
     * Returns null for super_admin (not gated) and cashier (read-only role).
     */
    protected static function cafeForCurrentUser(): ?Cafe
    {
        $user = Auth::user();

        if ($user?->role !== 'manager') {
            return null;
        }

        if (! filled($user->cafe_id)) {
            return null;
        }

        return Cafe::find($user->cafe_id);
    }

    protected static function subscriptionService(): SubscriptionService
    {
        return app(SubscriptionService::class);
    }

    /**
     * Super admin is never gated by subscription.
     */
    protected static function isSuperAdmin(): bool
    {
        return Auth::user()?->role === 'super_admin';
    }
}
