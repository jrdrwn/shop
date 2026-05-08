<?php

namespace App\Filament\Resources\Concerns;

use Illuminate\Support\Facades\Auth;
use UnitEnum;

trait HasRoleNavigation
{
    public static function getNavigationGroup(): string|UnitEnum|null
    {
        if (! property_exists(static::class, 'roleNavigationGroup')) {
            return null;
        }

        return static::$roleNavigationGroup;
    }

    public static function canAccess(): bool
    {
        $role = Auth::user()?->role;

        $allowedRoles = property_exists(static::class, 'allowedRoles')
            ? static::$allowedRoles
            : ['admin'];

        return is_string($role) && in_array($role, $allowedRoles, true);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
