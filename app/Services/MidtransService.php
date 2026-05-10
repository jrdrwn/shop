<?php

namespace App\Services;

use App\Models\Cafe;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MidtransService
{
    private string $serverKey;

    private bool $isProduction;

    private string $baseUrl;

    public function __construct()
    {
        $this->serverKey = (string) config('midtrans.server_key', '');
        $this->isProduction = (bool) config('midtrans.is_production', false);
        $this->baseUrl = $this->isProduction
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    /**
     * Create a Snap token for subscription upgrade.
     */
    public function createSnapToken(Cafe $cafe, Subscription $subscription): string
    {
        $orderId = 'SUB-'.Str::upper(Str::random(8)).'-'.$cafe->id;

        $payment = SubscriptionPayment::create([
            'cafe_id' => $cafe->id,
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
                'first_name' => $cafe->name,
                'email' => $cafe->email,
                'phone' => $cafe->phone,
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
    public function handleNotification(array $notification): SubscriptionPayment
    {
        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $signatureKey = $notification['signature_key'] ?? '';

        if (! $this->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            throw new \RuntimeException('Invalid Midtrans signature.');
        }

        $payment = SubscriptionPayment::where('order_id', $orderId)->firstOrFail();

        $transactionStatus = $notification['transaction_status'] ?? '';
        $paymentType = $notification['payment_type'] ?? null;
        $transactionId = $notification['transaction_id'] ?? null;
        $transactionTime = $notification['transaction_time'] ?? null;
        $settlementTime = $notification['settlement_time'] ?? null;

        $status = match ($transactionStatus) {
            'capture', 'settlement' => 'success',
            'pending' => 'pending',
            'deny', 'cancel', 'failure' => 'failed',
            'expire' => 'expire',
            default => 'pending',
        };

        $payment->update([
            'status' => $status,
            'payment_type' => $paymentType,
            'transaction_id' => $transactionId,
            'transaction_time' => $transactionTime,
            'settlement_time' => $settlementTime,
            'metadata' => array_merge($payment->metadata ?? [], $notification),
        ]);

        if ($status === 'success') {
            app(SubscriptionService::class)->activateSubscription(
                $payment->cafe,
                $payment->subscription,
                $transactionId ?? $orderId
            );
        }

        return $payment;
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
        return (string) config('midtrans.client_key', '');
    }
}
