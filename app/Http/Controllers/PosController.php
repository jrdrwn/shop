<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\InventoryLog;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Toko;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        if (! $user || $user->role !== 'kasir') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! $user->toko_id) {
            return response()->json(['message' => 'Akun kasir belum terhubung ke toko.'], 400);
        }

        $cart = $request->input('cart');
        $paymentMethod = $request->input('payment_method');
        $discountAmount = (int) $request->input('discount_amount');
        $paidAmount = (int) $request->input('paid_amount');
        $changeAmount = (int) $request->input('change_amount');

        // Tax & service are authoritative from the toko — never trust the client
        $toko = $user->toko;
        $taxRate = (int) ($toko?->tax_percentage ?? 0);
        $serviceRate = (int) ($toko?->service_charge_percentage ?? 0);

        try {
            return DB::transaction(function () use ($user, $cart, $paymentMethod, $taxRate, $serviceRate, $paidAmount, $changeAmount) {
                $subtotal = 0;
                $cartDetails = [];

                foreach ($cart as $item) {
                    $product = Product::whereId($item['id'])
                        ->where('toko_id', $user->toko_id)
                        ->first();

                    if (! $product) {
                        throw new \Exception("Produk #{$item['id']} tidak ditemukan untuk toko ini.");
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
                    'toko_id' => $user->toko_id,
                    'cashier_id' => $user->id,
                    'transaction_number' => 'TRX'.time().rand(1000, 9999),
                    'total_amount' => $totalAmount,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'service_charge_amount' => $serviceAmount,
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
                        'toko_id' => $user->toko_id,
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

                // Resolve or auto-create the payment method record for this toko
                $paymentMethodRecord = PaymentMethod::firstOrCreate(
                    ['toko_id' => $user->toko_id, 'type' => $paymentMethod],
                    ['name' => strtoupper($paymentMethod), 'is_active' => true]
                );

                $payment = Payment::create([
                    'transaction_id' => $transaction->id,
                    'payment_method_id' => $paymentMethodRecord->id,
                    'amount' => $paidAmount,
                    'reference_number' => "{$paymentMethod}-{$transaction->transaction_number}",
                    'status' => $paymentStatus,
                ]);

                // QRIS Logic: Handle Midtrans vs Manual
                $qrisData = null;
                Log::info('POS Checkout Debug', [
                    'received_payment_method' => $paymentMethod,
                    'toko_qris_type' => $toko->qris_type ?? 'N/A',
                    'toko_id' => $user->toko_id,
                ]);

                if ($paymentMethod === 'qris') {
                    $toko = Toko::find($user->toko_id);
                    if ($toko && $toko->qris_type === 'midtrans') {
                        try {
                            $midtransResponse = app(MidtransService::class)->generateQris($transaction);

                            // Find the QR code action in Midtrans response
                            $qrAction = collect($midtransResponse['actions'] ?? [])->where('name', 'generate-qr-code')->first();

                            $qrisData = [
                                'type' => 'midtrans',
                                'qr_url' => $qrAction['url'] ?? null,
                                'transaction_id' => $midtransResponse['transaction_id'] ?? null,
                                'expiry_time' => now()->addMinutes(15)->format('H:i'),
                            ];

                            // Save to payment metadata
                            $payment->update(['metadata' => $qrisData]);
                        } catch (\Exception $e) {
                            Log::error('Midtrans QRIS Error: '.$e->getMessage());
                        }
                    }
                }

                // Auto-record to Cash Flow for verified successful payments (e.g. Cash)
                if ($paymentStatus === 'success') {
                    CashFlow::create([
                        'toko_id' => $user->toko_id,
                        'type' => 'income',
                        'category' => 'sales',
                        'amount' => $totalAmount,
                        'description' => "Penjualan POS #{$transaction->transaction_number} (Tunai)",
                        'reference_id' => $transaction->id,
                        'reference_type' => 'transaction',
                        'created_by' => $user->id,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'transaction_id' => $transaction->id,
                    'qris_data' => $qrisData,
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

    public function checkStatus(string $transactionNumber)
    {
        $transaction = Transaction::where('transaction_number', $transactionNumber)
            ->where('toko_id', Auth::user()->toko_id)
            ->first();

        if (! $transaction) {
            return response()->json(['status' => 'not_found'], 404);
        }

        // If already completed in our DB, just return success
        if ($transaction->status === 'completed') {
            return response()->json(['status' => 'success']);
        }

        // If pending, try to check Midtrans directly for latest status
        try {
            $midtrans = app(MidtransService::class)->forToko($transaction->toko);
            $statusResponse = $midtrans->checkStatus($transactionNumber);

            $transactionStatus = $statusResponse['transaction_status'] ?? '';

            if (in_array($transactionStatus, ['capture', 'settlement'])) {
                $transaction->update(['status' => 'completed']);
                $transaction->payments()->update(['status' => 'success']);

                // Record to cash flow if not already done
                CashFlow::firstOrCreate(
                    ['reference_id' => $transaction->id, 'reference_type' => 'transaction'],
                    [
                        'toko_id' => $transaction->toko_id,
                        'type' => 'income',
                        'category' => 'sales',
                        'amount' => $transaction->total_amount,
                        'description' => "Penjualan POS #{$transaction->transaction_number} (Midtrans Check)",
                        'created_by' => $transaction->cashier_id,
                    ]
                );

                return response()->json(['status' => 'success']);
            }

            if (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'])) {
                $transaction->update(['status' => 'cancelled']);

                return response()->json(['status' => 'failed']);
            }

        } catch (\Exception $e) {
            // Ignore errors during check, just return current local status
        }

        return response()->json(['status' => $transaction->status]);
    }

    public function cancelOrder(string $transactionNumber)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $transaction = \App\Models\Transaction::where('transaction_number', $transactionNumber)
            ->where('toko_id', $user->toko_id)
            ->where('status', 'pending')
            ->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan atau sudah diproses.'], 404);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($transaction, $user) {
            $transaction->update(['status' => 'cancelled']);
            $transaction->payments()->update(['status' => 'failed']);

            foreach ($transaction->items as $item) {
                $product = $item->product;
                if ($product) {
                    $before = $product->stock;
                    $product->increment('stock', $item->quantity);
                    $after = $product->stock;

                    \App\Models\InventoryLog::create([
                        'toko_id' => $user->toko_id,
                        'product_id' => $product->id,
                        'action' => 'adjustment',
                        'quantity_change' => $item->quantity,
                        'quantity_before' => $before,
                        'quantity_after' => $after,
                        'reference_id' => $transaction->id,
                        'reference_type' => 'transaction',
                        'notes' => "POS Order Cancelled - Stock Returned (#{$transaction->transaction_number})",
                        'created_by' => $user->id,
                    ]);
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Pesanan berhasil dibatalkan dan stok telah kembali.']);
    }
}
