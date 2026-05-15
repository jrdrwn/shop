<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Toko;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MidtransService
{
    private string $clientKey;

    public function __construct()
    {
        $this->serverKey = (string) config('midtrans.server_key', '');
        $this->clientKey = (string) config('midtrans.client_key', '');
        $this->isProduction = (bool) config('midtrans.is_production', false);
        $this->baseUrl = $this->isProduction
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    /**
     * Configure the service for a specific toko's Midtrans credentials.
     */
    public function forToko(Toko $toko): self
    {
        $clone = clone $this;
        
        // If toko has its own server key, use it. Otherwise fall back to system default.
        if (filled($toko->midtrans_server_key)) {
            $clone->serverKey = $toko->midtrans_server_key;
            $clone->clientKey = $toko->midtrans_client_key ?? '';
            $clone->isProduction = (bool) $toko->midtrans_is_production;
            $clone->baseUrl = $clone->isProduction
                ? 'https://api.midtrans.com'
                : 'https://api.sandbox.midtrans.com';

            \Illuminate\Support\Facades\Log::info("Using TOKO Midtrans Keys for Shop: {$toko->name}", [
                'server_key' => \Illuminate\Support\Str::mask($clone->serverKey, '*', 6, -6),
                'client_key' => \Illuminate\Support\Str::mask($clone->clientKey, '*', 6, -6),
                'is_production' => $clone->isProduction
            ]);
        } else {
            \Illuminate\Support\Facades\Log::info("Using SYSTEM Default Midtrans Keys for Shop: {$toko->name}");
        }

        return $clone;
    }

    /**
     * Generate a QRIS code for a transaction.
     */
    public function generateQris(\App\Models\Transaction $transaction): array
    {
        $toko = $transaction->toko;
        $orderId = $transaction->transaction_number;

        $items = $transaction->items->map(function($item) {
            return [
                'id' => (string) $item->product_id,
                'price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'name' => substr($item->product->name ?? 'Item', 0, 50),
            ];
        })->toArray();

        // Add Tax as item if > 0
        if ($transaction->tax_amount > 0) {
            $items[] = [
                'id' => 'TAX',
                'price' => (int) $transaction->tax_amount,
                'quantity' => 1,
                'name' => 'Pajak (Tax)',
            ];
        }

        // Add Service Charge as item if > 0
        if ($transaction->service_charge_amount > 0) {
            $items[] = [
                'id' => 'SERVICE',
                'price' => (int) $transaction->service_charge_amount,
                'quantity' => 1,
                'name' => 'Biaya Layanan',
            ];
        }

        // Add Discount as item if > 0 (negative price)
        if ($transaction->discount_amount > 0) {
            $items[] = [
                'id' => 'DISCOUNT',
                'price' => -(int) $transaction->discount_amount,
                'quantity' => 1,
                'name' => 'Diskon',
            ];
        }

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $transaction->total_amount,
            ],
            'expiry' => [
                'unit' => 'minutes',
                'duration' => 15,
            ],
        ];

        $service = $this->forToko($toko);
        
        $response = Http::timeout(10)
            ->withBasicAuth($service->serverKey, '')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post("{$service->baseUrl}/v2/charge", $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal generate QRIS Midtrans: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Create a Snap token for a POS transaction.
     */
    public function createTransactionSnapToken(\App\Models\Transaction $transaction): string
    {
        $toko = $transaction->toko;
        $orderId = $transaction->transaction_number;

        $items = $transaction->items->map(function($item) {
            return [
                'id' => (string) $item->product_id,
                'price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'name' => substr($item->product->name ?? 'Item', 0, 50),
            ];
        })->toArray();

        // Add Tax as item if > 0
        if ($transaction->tax_amount > 0) {
            $items[] = [
                'id' => 'TAX',
                'price' => (int) $transaction->tax_amount,
                'quantity' => 1,
                'name' => 'Pajak (Tax)',
            ];
        }

        // Add Service Charge as item if > 0
        if ($transaction->service_charge_amount > 0) {
            $items[] = [
                'id' => 'SERVICE',
                'price' => (int) $transaction->service_charge_amount,
                'quantity' => 1,
                'name' => 'Biaya Layanan',
            ];
        }

        // Add Discount as item if > 0 (negative price)
        if ($transaction->discount_amount > 0) {
            $items[] = [
                'id' => 'DISCOUNT',
                'price' => -(int) $transaction->discount_amount,
                'quantity' => 1,
                'name' => 'Diskon',
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $transaction->total_amount,
            ],
            'customer_details' => [
                'first_name' => $toko->name . ' Customer',
            ],
            'item_details' => $items,
            'enabled_payments' => ['qris', 'gopay', 'shopeepay', 'other_qris'],
        ];

        $service = $this->forToko($toko);

        $response = Http::timeout(10)
            ->withBasicAuth($service->serverKey, '')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post("{$service->baseUrl}/snap/v1/transactions", $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal membuat Snap Token POS: ' . $response->body());
        }

        return $response->json()['token'];
    }

    /**
     * Create a Snap token for subscription upgrade.
     */
    public function createSnapToken(Toko $toko, Subscription $subscription): string
    {
        $orderId = 'SUB-'.Str::upper(Str::random(8)).'-'.$toko->id;

        $payment = SubscriptionPayment::create([
            'toko_id' => $toko->id,
            'subscription_id' => $subscription->id,
            'order_id' => $orderId,
            'amount' => $subscription->price,
            'status' => 'pending',
        ]);

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $subscription->price,
            ],
            'customer_details' => [
                'first_name' => $toko->name,
                'email' => $toko->email,
                'phone' => $toko->phone,
            ],
            'item_details' => [
                [
                    'id' => (string) $subscription->id,
                    'price' => (int) $subscription->price,
                    'quantity' => 1,
                    'name' => substr($subscription->name, 0, 50),
                ],
            ],
        ];

        $response = Http::timeout(10)
            ->withBasicAuth($this->serverKey, '')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/snap/v1/transactions", $payload);

        if (! $response->successful()) {
            $payment->update(['status' => 'failed', 'metadata' => ['error' => $response->body()]]);
            throw new \RuntimeException('Gagal membuat transaksi Midtrans: '.$response->body());
        }

        $data = $response->json();

        $payment->update([
            'metadata' => array_merge($payment->metadata ?? [], [
                'snap_token' => $data['token'] ?? null,
                'redirect_url' => $data['redirect_url'] ?? null,
            ]),
        ]);

        return $data['token'];
    }

    /**
     * Handle Midtrans notification webhook.
     *
     * @param  array<string, mixed>  $notification
     */
    public function handleNotification(array $notification): mixed
    {
        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $signatureKey = $notification['signature_key'] ?? '';

        // Determine if this is a Subscription or POS Transaction
        if (str_starts_with($orderId, 'SUB-')) {
            // 1. Subscription Payment (Uses System Server Key)
            if (! $this->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
                throw new \RuntimeException('Invalid Midtrans signature for subscription.');
            }

            return $this->processSubscriptionNotification($notification);
        } else {
            // 2. POS Transaction (Uses Toko Server Key)
            // Try to find the transaction by its number (order_id)
            $transaction = \App\Models\Transaction::where('transaction_number', $orderId)->first();
            
            if (!$transaction) {
                // Fallback: search for reference number in payments
                $paymentRecord = \App\Models\Payment::where('reference_number', 'LIKE', "%{$orderId}%")->first();
                $transaction = $paymentRecord?->transaction;
            }

            if (!$transaction || !$transaction->toko) {
                throw new \RuntimeException("Transaction not found for order ID: {$orderId}");
            }

            $toko = $transaction->toko;
            $tokoService = $this->forToko($toko);

            if (! $tokoService->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
                throw new \RuntimeException('Invalid Midtrans signature for toko transaction.');
            }

            return $this->processTransactionNotification($transaction, $notification);
        }
    }

    /**
     * Process internal subscription payment logic.
     */
    private function processSubscriptionNotification(array $notification): SubscriptionPayment
    {
        $orderId = $notification['order_id'] ?? '';
        $payment = SubscriptionPayment::where('order_id', $orderId)->firstOrFail();

        $transactionStatus = $notification['transaction_status'] ?? '';
        $status = $this->mapMidtransStatus($transactionStatus);

        $payment->update([
            'status' => $status,
            'payment_type' => $notification['payment_type'] ?? null,
            'transaction_id' => $notification['transaction_id'] ?? null,
            'transaction_time' => $notification['transaction_time'] ?? null,
            'settlement_time' => $notification['settlement_time'] ?? null,
            'metadata' => array_merge($payment->metadata ?? [], $notification),
        ]);

        if ($status === 'success') {
            app(SubscriptionService::class)->activateSubscription(
                $payment->toko,
                $payment->subscription,
                $notification['transaction_id'] ?? $orderId
            );
        }

        return $payment;
    }

    /**
     * Process internal POS transaction payment logic.
     */
    private function processTransactionNotification(\App\Models\Transaction $transaction, array $notification): \App\Models\Transaction
    {
        $transactionStatus = $notification['transaction_status'] ?? '';
        $status = $this->mapMidtransStatus($transactionStatus);

        if ($status === 'success') {
            $transaction->update(['status' => 'completed']);
            
            // Update the specific payment record
            $paymentRecord = $transaction->payments()->where('status', 'pending')->first();
            if ($paymentRecord) {
                $paymentRecord->update([
                    'status' => 'success',
                    'reference_number' => $notification['transaction_id'] ?? $paymentRecord->reference_number,
                ]);
            }

            // Record to Cash Flow automatically upon success
            \App\Models\CashFlow::firstOrCreate(
                ['reference_id' => $transaction->id, 'reference_type' => 'transaction'],
                [
                    'toko_id' => $transaction->toko_id,
                    'type' => 'income',
                    'category' => 'sales',
                    'amount' => $transaction->total_amount,
                    'description' => "Penjualan POS #{$transaction->transaction_number} (Midtrans Auto)",
                    'created_by' => $transaction->cashier_id ?? $transaction->toko->owner?->id,
                ]
            );
        } elseif ($status === 'failed' || $status === 'expire') {
            $transaction->update(['status' => 'cancelled']);
            $transaction->payments()->update(['status' => 'failed']);
        }

        return $transaction;
    }

    private function mapMidtransStatus(string $transactionStatus): string
    {
        return match ($transactionStatus) {
            'capture', 'settlement' => 'success',
            'pending' => 'pending',
            'deny', 'cancel', 'failure' => 'failed',
            'expire' => 'expire',
            default => 'pending',
        };
    }

    /**
     * Check transaction status from Midtrans API.
     */
    public function checkStatus(string $orderId): array
    {
        $response = Http::timeout(10)
            ->withBasicAuth($this->serverKey, '')
            ->get("{$this->baseUrl}/v2/{$orderId}/status");

        return $response->json() ?? [];
    }

    /**
     * Verify Midtrans notification signature.
     */
    private function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        return hash_equals($expected, $signatureKey);
    }

    /**
     * Get the Snap JS URL.
     */
    public function snapUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /**
     * Get the client key.
     */
    public function clientKey(): string
    {
        return $this->clientKey;
    }
}
