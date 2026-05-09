<?php

namespace App\Http\Controllers;

use App\Models\InventoryLog;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer|exists:products,id',
            'cart.*.qty' => 'required|integer|min:1',
            'cart.*.notes' => 'nullable|string|max:255',
            'payment_method' => 'required|string|in:cash,debit,qris',
            'discount_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'change_amount' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        if (! $user || $user->role !== 'cashier') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! $user->cafe_id) {
            return response()->json(['message' => 'Akun kasir belum terhubung ke cafe.'], 400);
        }

        $cart = $request->input('cart');
        $paymentMethod = $request->input('payment_method');
        $discountAmount = (int) $request->input('discount_amount');
        $paidAmount = (int) $request->input('paid_amount');
        $changeAmount = (int) $request->input('change_amount');

        // Tax & service are authoritative from the cafe — never trust the client
        $cafe = $user->cafe;
        $taxRate = (int) ($cafe?->tax_percentage ?? 0);
        $serviceRate = (int) ($cafe?->service_charge_percentage ?? 0);

        try {
            return DB::transaction(function () use ($user, $cart, $paymentMethod, $taxRate, $serviceRate, $paidAmount, $changeAmount) {
                $subtotal = 0;
                $cartDetails = [];

                foreach ($cart as $item) {
                    $product = Product::where('id', $item['id'])
                        ->where('cafe_id', $user->cafe_id)
                        ->first();

                    if (! $product) {
                        throw new \Exception("Produk #{$item['id']} tidak ditemukan untuk cafe ini.");
                    }

                    $qty = (int) $item['qty'];
                    if ($qty > $product->stock) {
                        throw new \Exception("Stok tidak cukup untuk produk: {$product->name}.");
                    }

                    $itemSubtotal = $product->price * $qty;
                    $subtotal += $itemSubtotal;

                    $cartDetails[] = [
                        'product' => $product,
                        'qty' => $qty,
                        'price' => $product->price,
                        'discount_pct' => (int) $product->discount_percentage,
                        'subtotal' => $itemSubtotal,
                        'notes' => $item['notes'] ?? null,
                    ];
                }

                $calculatedDiscount = 0;
                foreach ($cartDetails as $detail) {
                    $calculatedDiscount += (int) round($detail['price'] * ($detail['discount_pct'] / 100)) * $detail['qty'];
                }

                $netSubtotal = $subtotal - $calculatedDiscount;
                $taxAmount = (int) round($netSubtotal * $taxRate / 100);
                $serviceAmount = (int) round($netSubtotal * $serviceRate / 100);
                $discountAmount = $calculatedDiscount;
                $totalAmount = $netSubtotal + $taxAmount + $serviceAmount;

                if ($paidAmount < $totalAmount) {
                    throw new \Exception('Jumlah pembayaran kurang dari total.');
                }

                $changeAmount = max(0, $paidAmount - $totalAmount);

                // Transaction status mirrors payment settlement:
                // cash = completed immediately; debit/qris = pending until confirmed
                $transactionStatus = $paymentMethod === 'cash' ? 'completed' : 'pending';

                $transaction = Transaction::create([
                    'cafe_id' => $user->cafe_id,
                    'cashier_id' => $user->id,
                    'transaction_number' => 'TRX'.time().rand(1000, 9999),
                    'total_amount' => $totalAmount,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'status' => $transactionStatus,
                    'notes' => "POS checkout - {$paymentMethod} payment",
                ]);

                foreach ($cartDetails as $detail) {
                    $product = $detail['product'];
                    $qty = $detail['qty'];

                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $detail['price'],
                        'subtotal' => $detail['subtotal'],
                        'notes' => $detail['notes'],
                    ]);

                    $before = $product->stock;
                    $product->decrement('stock', $qty);
                    $after = $product->stock;

                    InventoryLog::create([
                        'cafe_id' => $user->cafe_id,
                        'product_id' => $product->id,
                        'action' => 'sale',
                        'quantity_change' => -$qty,
                        'quantity_before' => $before,
                        'quantity_after' => $after,
                        'reference_id' => $transaction->id,
                        'reference_type' => 'transaction',
                        'notes' => "POS sale - {$paymentMethod}",
                        'created_by' => $user->id,
                    ]);
                }

                $paymentStatus = match ($paymentMethod) {
                    'cash' => 'success',
                    'debit' => 'pending',
                    'qris' => 'pending',
                    default => 'pending'
                };

                // Resolve or auto-create the payment method record for this cafe
                $paymentMethodRecord = PaymentMethod::firstOrCreate(
                    ['cafe_id' => $user->cafe_id, 'type' => $paymentMethod],
                    ['name' => strtoupper($paymentMethod), 'is_active' => true]
                );

                Payment::create([
                    'transaction_id' => $transaction->id,
                    'payment_method_id' => $paymentMethodRecord->id,
                    'amount' => $paidAmount,
                    'reference_number' => "{$paymentMethod}-{$transaction->transaction_number}",
                    'status' => $paymentStatus,
                ]);

                return response()->json([
                    'success' => true,
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'total_amount' => $totalAmount,
                    'change_amount' => $changeAmount,
                    'payment_method' => $paymentMethod,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
