<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function receipt(Transaction $transaction)
    {
        $user = Auth::user();
        
        // Check if user is authorized to see this transaction
        if ($user->role === 'manager' && $user->cafe_id !== $transaction->cafe_id) {
            abort(403);
        }
        
        if ($user->role === 'cashier' && $user->id !== $transaction->cashier_id) {
            abort(403);
        }

        $transaction->load(['items.product', 'cashier', 'cafe']);

        return view('transactions.receipt', compact('transaction'));
    }
}
