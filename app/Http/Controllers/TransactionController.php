<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function receipt(Transaction $transaction)
    {
        $user = Auth::user();

        // Check if user is authorized to see this transaction
        if (($user->role === UserRole::Owner->value || $user->role === 'owner') && $user->toko_id !== $transaction->toko_id) {
            abort(403);
        }

        if ($user->role === 'kasir' && $user->id !== $transaction->cashier_id) {
            abort(403);
        }

        $transaction->load(['items.product', 'cashier', 'toko']);

        return view('transactions.receipt', compact('transaction'));
    }
}
