<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Toko;
use App\Models\User;

class TokoPolicy
{
    public function before(User $user)
    {
        if ($user->role === 'super_admin') {
            return true;
        }
    }

    public function view(User $user, Toko $toko): bool
    {
        if ($user->role === UserRole::Owner->value || $user->role === 'owner') {
            return $user->toko_id === $toko->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    public function update(User $user, Toko $toko): bool
    {
        if ($user->role === UserRole::Owner->value || $user->role === 'owner') {
            return $user->toko_id === $toko->id;
        }

        return false;
    }

    public function delete(User $user, Toko $toko): bool
    {
        return $user->role === 'super_admin';
    }
}
