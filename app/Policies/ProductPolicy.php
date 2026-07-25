<?php

namespace App\Policies;

use App\Enums\UserRole;
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
        if (in_array($user->role, [UserRole::Owner->value, 'owner', UserRole::Warehouse->value, 'gudang'])) {
            return (int) $user->toko_id === (int) $product->toko_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', UserRole::Owner->value, 'owner', UserRole::Warehouse->value, 'gudang']);
    }

    public function update(User $user, Product $product): bool
    {
        if (in_array($user->role, [UserRole::Owner->value, 'owner', UserRole::Warehouse->value, 'gudang'])) {
            return (int) $user->toko_id === (int) $product->toko_id;
        }

        return false;
    }

    public function delete(User $user, Product $product): bool
    {
        if (in_array($user->role, [UserRole::Owner->value, 'owner', UserRole::Warehouse->value, 'gudang'])) {
            return (int) $user->toko_id === (int) $product->toko_id;
        }

        return false;
    }
}
