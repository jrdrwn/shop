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
use App\Services\IpaymuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    public function checkout(Request $request): JsonResponse
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
                        ->where('is_active', true)
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
                    'reference_number' => $paymentMethod === 'qris'
                        ? $transaction->transaction_number
                        : "{$paymentMethod}-{$transaction->transaction_number}",
                    'status' => $paymentStatus,
                ]);

                // QRIS Logic: Handle iPaymu vs Manual
                $qrisData = null;
                $toko = Toko::find($user->toko_id);
                Log::info('POS Checkout Debug', [
                    'received_payment_method' => $paymentMethod,
                    'toko_qris_type' => $toko->qris_type ?? 'N/A',
                    'toko_id' => $user->toko_id,
                ]);

                if ($paymentMethod === 'qris') {
                    $effectiveQrisType = $toko->qris_type ?? (filled($toko->ipaymu_va) ? 'ipaymu' : 'manual');

                    if ($effectiveQrisType === 'ipaymu') {
                        try {
                            $ipaymuResponse = app(IpaymuService::class)->generateQris($transaction);

                            $qrisData = [
                                'type' => 'ipaymu',
                                'reference_id' => $ipaymuResponse['reference_id'],
                                'qr_url' => $ipaymuResponse['qr_url'],
                                'checkout_url' => $ipaymuResponse['checkout_url'],
                                'transaction_id' => $ipaymuResponse['transaction_id'],
                                'expiry_time' => now()->addMinutes(15)->format('H:i'),
                            ];

                            if (empty($qrisData['checkout_url']) && empty($qrisData['qr_url'])) {
                                throw new \Exception('iPaymu tidak memberikan URL atau kode QRIS. Cek konfigurasi pembayaran.');
                            }

                            $payment->update([
                                'metadata' => $qrisData,
                                'gateway_transaction_id' => $qrisData['transaction_id'],
                            ]);
                        } catch (\Exception $e) {
                            Log::error('iPaymu QRIS Error: '.$e->getMessage());
                            throw new \Exception('Gagal terhubung ke iPaymu: '.$e->getMessage());
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

    public function checkStatus(string $transactionNumber): JsonResponse
    {
        $transaction = Transaction::where('transaction_number', $transactionNumber)
            ->where('toko_id', Auth::user()->toko_id)
            ->first();

        if (! $transaction) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json(['status' => match ($transaction->status) {
            'completed' => 'success',
            'cancelled' => 'failed',
            default => 'pending',
        }]);
    }

    public function cancelOrder(string $transactionNumber): JsonResponse
    {
        $user = Auth::user();
        $transaction = Transaction::where('transaction_number', $transactionNumber)
            ->where('toko_id', $user->toko_id)
            ->where('status', 'pending')
            ->first();

        if (! $transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan atau sudah diproses.'], 404);
        }

        if ($transaction->payments()->whereNotNull('gateway_transaction_id')->exists()) {
            return response()->json(['message' => 'Pembayaran iPaymu sedang diproses. Tunggu konfirmasi gateway.'], 409);
        }

        DB::transaction(function () use ($transaction, $user) {
            $transaction->update(['status' => 'cancelled']);
            $transaction->payments()->update(['status' => 'failed']);

            foreach ($transaction->items as $item) {
                $product = $item->product;
                if ($product) {
                    $before = $product->stock;
                    $product->increment('stock', $item->quantity);
                    $after = $product->stock;

                    InventoryLog::create([
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

    public function handleIpaymuNotification(Request $request): JsonResponse
    {
        try {
            app(IpaymuService::class)->handleNotification($request->all(), $request->headers->all());

            return response()->json(['message' => 'OK']);
        } catch (\Throwable $exception) {
            Log::error('iPaymu POS notification failed', ['error' => $exception->getMessage()]);

            return response()->json(['message' => $exception->getMessage()], 400);
        }
    }

    public function finish(Request $request): RedirectResponse
    {
        Log::info('iPaymu POS finish redirect triggered', ['order_id' => $request->input('order_id')]);

        return redirect('/cashier')
            ->with('info', 'Status pembayaran akan diperbarui setelah notifikasi iPaymu diterima.');
    }
}
