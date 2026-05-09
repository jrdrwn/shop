<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function before(User $user)
    {
        if ($user->role === 'super_admin') {
            return true;
        }
    }

    public function view(User $user, Product $product): bool
    {
        if ($user->role === 'manager') {
            return $user->cafe_id === $product->cafe_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'manager']);
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->role === 'manager') {
            return $user->cafe_id === $product->cafe_id;
        }

        return false;
    }

    public function delete(User $user, Product $product): bool
    {
        if ($user->role === 'manager') {
            return $user->cafe_id === $product->cafe_id;
        }

        return false;
    }
}
