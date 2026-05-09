<?php

namespace App\Policies;

use App\Models\Cafe;
use App\Models\User;

class CafePolicy
{
    public function before(User $user)
    {
        if ($user->role === 'super_admin') {
            return true;
        }
    }

    public function view(User $user, Cafe $cafe): bool
    {
        if ($user->role === 'manager') {
            return $user->cafe_id === $cafe->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    public function update(User $user, Cafe $cafe): bool
    {
        if ($user->role === 'manager') {
            return $user->cafe_id === $cafe->id;
        }

        return false;
    }

    public function delete(User $user, Cafe $cafe): bool
    {
        return $user->role === 'super_admin';
    }
}
