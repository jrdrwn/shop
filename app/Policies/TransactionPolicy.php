<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function before(User $user)
    {
        if ($user->role === 'super_admin') {
            return true;
        }
    }

    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->role === 'manager') {
            return $user->cafe_id === $transaction->cafe_id;
        }
        if ($user->role === 'cashier') {
            return $user->id === $transaction->cashier_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'manager', 'cashier']);
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->role === 'super_admin';
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->role === 'super_admin';
    }
}
