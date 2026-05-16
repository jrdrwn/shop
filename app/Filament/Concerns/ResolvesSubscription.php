<?php

namespace App\Filament\Concerns;

use App\Models\Toko;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;

/**
 * Shared helpers for subscription-gated Filament resources and list pages.
 */
trait ResolvesSubscription
{
    /**
     * Resolve the toko for the currently logged-in owner or gudang.
     * Returns null for super_admin (not gated) and cashier (read-only role).
     */
    protected static function tokoForCurrentUser(): ?Toko
    {
        $user = Auth::user();

        if (! in_array($user?->role, ['owner', 'gudang'], true)) {
            return null;
        }

        if (! filled($user->toko_id)) {
            return null;
        }

        return Toko::find($user->toko_id);
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
