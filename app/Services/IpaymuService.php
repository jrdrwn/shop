<?php

namespace App\Services;

use App\Models\CashFlow;
use App\Models\InventoryLog;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Toko;
use App\Models\Transaction;
use chillerlan\QRCode\QRCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IpaymuService
{
    private string $va;

    private string $apiKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->va = (string) config('ipaymu.va', '');
        $this->apiKey = (string) config('ipaymu.api_key', '');
        $this->baseUrl = (string) config('ipaymu.api_url');
    }

    public function forToko(Toko $toko): self
    {
        $service = clone $this;

        if (filled($toko->ipaymu_va)) {
            $service->va = (string) $toko->ipaymu_va;
            $service->apiKey = (string) ($toko->ipaymu_api_key ?? '');
        }

        return $service;
    }

    /**
     * Create an iPaymu direct QRIS payment for a POS transaction.
     *
     * @return array{reference_id: string, transaction_id: string, qr_url: string, checkout_url: string}
     */
    public function generateQris(Transaction $transaction): array
    {
        $service = $this->forToko($transaction->toko);
        $orderId = $transaction->transaction_number;
        $response = $service->callApi('POST', '/payment/direct', [
            'name' => $transaction->toko->name,
            'phone' => (string) ($transaction->toko->phone ?? '0000000000'),
            'email' => (string) ($transaction->toko->email ?? 'billing@example.com'),
            'amount' => (int) $transaction->total_amount,
            'notifyUrl' => $service->notificationUrl('pos.ipaymu.notification'),
            'comments' => 'Pembayaran POS '.$orderId,
            'referenceId' => $orderId,
            'paymentMethod' => 'qris',
            'paymentChannel' => 'mpm',
            'product' => ['Pesanan '.$orderId],
            'qty' => [1],
            'price' => [(int) $transaction->total_amount],
        ]);

        $data = $response['Data'] ?? [];
        $returnedReferenceId = (string) ($data['ReferenceId'] ?? '');
        if ($returnedReferenceId !== '' && $returnedReferenceId !== $orderId) {
            throw new \RuntimeException('Reference ID iPaymu tidak sesuai dengan transaksi POS.');
        }

        $checkoutUrl = (string) ($data['Url'] ?? '');
        $transactionId = (string) ($data['TransactionId'] ?? '');
        if ($transactionId === '') {
            throw new \RuntimeException('iPaymu tidak mengembalikan ID transaksi untuk Tes Notify.');
        }

        $qr = (string) ($data['Qr'] ?? $data['QR'] ?? $data['QrCode'] ?? $data['QRCode']
            ?? $data['QrString'] ?? $data['QRString'] ?? $data['QrUrl'] ?? $data['QRUrl'] ?? '');
        $qrUrl = '';

        if ($qr !== '') {
            $qrUrl = str_starts_with($qr, 'https://') || str_starts_with($qr, 'http://') || str_starts_with($qr, 'data:image/')
                ? $qr
                : (new QRCode)->render($qr);
        }

        if ($checkoutUrl === '' && $qrUrl === '') {
            Log::warning('iPaymu QRIS response has no payment URL or QR', [
                'order_id' => $orderId,
                'data_keys' => array_keys(is_array($data) ? $data : []),
                'message' => $response['Message'] ?? null,
            ]);

            throw new \RuntimeException('iPaymu tidak mengembalikan URL atau kode QRIS.');
        }

        return [
            'reference_id' => $orderId,
            'transaction_id' => $transactionId,
            'qr_url' => $qrUrl,
            'checkout_url' => $checkoutUrl,
        ];
    }

    public function createPaymentUrl(Toko $toko, Subscription $subscription): string
    {
        $orderId = 'SUB-'.Str::upper(Str::random(8)).'-'.$toko->id;
        $payment = SubscriptionPayment::create([
            'toko_id' => $toko->id,
            'subscription_id' => $subscription->id,
            'order_id' => $orderId,
            'amount' => $subscription->price,
            'status' => 'pending',
        ]);

        try {
            $response = $this->forToko($toko)->callApi('POST', '/payment', [
                'product' => [$subscription->name],
                'qty' => [1],
                'price' => [(int) $subscription->price],
                'description' => ['Upgrade langganan '.$subscription->name],
                'returnUrl' => route('subscription.finish').'?order_id='.$orderId,
                'notifyUrl' => $this->notificationUrl('subscription.ipaymu.notification'),
                'cancelUrl' => route('subscription.error').'?order_id='.$orderId,
                'referenceId' => $orderId,
                'buyerName' => $toko->name,
                'buyerEmail' => $toko->email,
                'buyerPhone' => $toko->phone,
                'expired' => 1,
            ]);

            $checkoutUrl = (string) ($response['Data']['Url'] ?? '');
            if ($checkoutUrl === '') {
                throw new \RuntimeException('Response iPaymu tidak memiliki URL pembayaran.');
            }

            $payment->update([
                'metadata' => array_merge($payment->metadata ?? [], [
                    'checkout_url' => $checkoutUrl,
                    'session_id' => $response['Data']['SessionID'] ?? null,
                ]),
            ]);

            return $checkoutUrl;
        } catch (\Throwable $exception) {
            $payment->update([
                'status' => 'failed',
                'metadata' => array_merge($payment->metadata ?? [], ['error' => $exception->getMessage()]),
            ]);

            throw new \RuntimeException('Gagal membuat transaksi iPaymu: '.$exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Process iPaymu callback for both subscriptions and POS transactions.
     *
     * @return array{status: string, reference_id: string, transaction_id: string|null}
     */
    public function handleNotification(array $payload, array $headers): array
    {
        $referenceId = (string) ($payload['reference_id'] ?? $payload['referenceId'] ?? '');
        $status = $this->mapStatus($payload);
        $transactionId = isset($payload['trx_id']) ? (string) $payload['trx_id'] : null;

        $subscriptionPayment = SubscriptionPayment::where('order_id', $referenceId)->first();
        $transaction = Transaction::where('transaction_number', $referenceId)->first();

        if (! $subscriptionPayment && ! $transaction) {
            throw new \RuntimeException('iPaymu payment reference not found.');
        }

        $this->forToko(($subscriptionPayment?->toko ?? $transaction->toko))->verifyCallbackSignature($payload, $headers);

        DB::transaction(function () use ($referenceId, $status, $transactionId, $payload, $subscriptionPayment, $transaction): void {
            if ($subscriptionPayment) {
                $payment = SubscriptionPayment::whereKey($subscriptionPayment->id)->lockForUpdate()->firstOrFail();

                if ($payment->status !== 'success') {
                    $payment->update([
                        'status' => $status,
                        'payment_type' => $payload['channel'] ?? $payload['via'] ?? null,
                        'transaction_id' => $transactionId ?? $payment->transaction_id,
                        'settlement_time' => $status === 'success' ? now() : $payment->settlement_time,
                        'metadata' => array_merge($payment->metadata ?? [], $payload),
                    ]);

                    if ($status === 'success') {
                        app(SubscriptionService::class)->activateSubscription(
                            $payment->toko,
                            $payment->subscription,
                            $transactionId ?? $referenceId
                        );
                    }
                }
            }

            if ($transaction) {
                $order = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
                $payment = $order->payments()->whereHas('paymentMethod', fn ($query) => $query->where('type', 'qris'))->first();

                if (! $payment || ($transactionId !== null && $payment->gateway_transaction_id !== null
                    && $payment->gateway_transaction_id !== $transactionId)) {
                    throw new \RuntimeException('iPaymu POS transaction ID does not match the payment.');
                }

                if ($order->status === 'pending') {
                    $order->update(['status' => $status === 'success' ? 'completed' : ($status === 'failed' ? 'cancelled' : 'pending')]);
                    $payment->update([
                        'status' => $status,
                        'gateway_transaction_id' => $transactionId ?? $payment->gateway_transaction_id,
                    ]);

                    if ($status === 'success') {
                        CashFlow::firstOrCreate(
                            ['reference_id' => $order->id, 'reference_type' => 'transaction'],
                            [
                                'toko_id' => $order->toko_id,
                                'type' => 'income',
                                'category' => 'sales',
                                'amount' => $order->total_amount,
                                'description' => "Penjualan POS #{$order->transaction_number} (iPaymu)",
                                'created_by' => $order->cashier_id,
                            ]
                        );
                    }

                    if ($status === 'failed') {
                        foreach ($order->items as $item) {
                            $product = $item->product;
                            if (! $product) {
                                continue;
                            }

                            $before = $product->stock;
                            $product->increment('stock', $item->quantity);

                            InventoryLog::create([
                                'toko_id' => $order->toko_id,
                                'product_id' => $product->id,
                                'action' => 'adjustment',
                                'quantity_change' => $item->quantity,
                                'quantity_before' => $before,
                                'quantity_after' => $product->stock,
                                'reference_id' => $order->id,
                                'reference_type' => 'transaction',
                                'notes' => "POS payment failed (#{$order->transaction_number})",
                                'created_by' => $order->cashier_id,
                            ]);
                        }
                    }
                }
            }
        });

        return [
            'status' => $status,
            'reference_id' => $referenceId,
            'transaction_id' => $transactionId,
        ];
    }

    public function reconcileSubscriptionPayment(SubscriptionPayment $payment): bool
    {
        if ($payment->status === 'success') {
            return true;
        }

        $sessionId = (string) ($payment->metadata['session_id'] ?? '');
        if ($sessionId === '') {
            return false;
        }

        $response = $this->forToko($payment->toko)->callApi('POST', '/transaction', [
            'transactionId' => $sessionId,
        ]);
        $data = $response['Data'] ?? [];

        if ((string) ($data['ReferenceId'] ?? '') !== $payment->order_id
            || (int) ($data['SubTotal'] ?? -1) !== (int) $payment->amount) {
            throw new \RuntimeException('iPaymu transaction does not match the subscription payment.');
        }

        if (strtolower((string) ($data['PaidStatus'] ?? '')) !== 'paid'
            || ! in_array((int) ($data['Status'] ?? 0), [1, 6, 7], true)) {
            return false;
        }

        $transactionId = (string) ($data['TransactionId'] ?? '');
        if ($transactionId === '') {
            throw new \RuntimeException('iPaymu did not return a transaction ID.');
        }

        DB::transaction(function () use ($payment, $transactionId, $data): void {
            $lockedPayment = SubscriptionPayment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->status === 'success') {
                return;
            }

            $lockedPayment->update([
                'status' => 'success',
                'transaction_id' => $transactionId,
                'settlement_time' => now(),
                'metadata' => array_merge($lockedPayment->metadata ?? [], [
                    'verified_status' => $data['Status'],
                    'verified_paid_status' => $data['PaidStatus'],
                ]),
            ]);

            app(SubscriptionService::class)->activateSubscription(
                $lockedPayment->toko,
                $lockedPayment->subscription,
                $transactionId
            );
        });

        return true;
    }

    private function notificationUrl(string $routeName): string
    {
        $baseUrl = (string) config('ipaymu.callback_base_url', '');

        if ($baseUrl === '') {
            return route($routeName);
        }

        return rtrim($baseUrl, '/').'/'.ltrim((string) parse_url(route($routeName), PHP_URL_PATH), '/');
    }

    private function callApi(string $method, string $path, array $body): array
    {
        if ($this->va === '' || $this->apiKey === '') {
            throw new \RuntimeException('iPaymu VA dan API key belum dikonfigurasi.');
        }

        $jsonBody = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $bodyHash = hash('sha256', $jsonBody);
        $stringToSign = strtoupper($method).':'.$this->va.':'.$bodyHash.':'.$this->apiKey;
        $signature = hash_hmac('sha256', $stringToSign, $this->apiKey);
        $timestamp = now()->format('YmdHis');

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'va' => $this->va,
            'signature' => $signature,
            'timestamp' => $timestamp,
        ];

        $request = Http::timeout(15)->connectTimeout(5);
        if (filled(config('ipaymu.ca_bundle'))) {
            $request = $request->withOptions(['verify' => config('ipaymu.ca_bundle')]);
        }

        $response = $request
            ->withHeaders($headers)
            ->withBody($jsonBody, 'application/json')
            ->post($this->baseUrl.$path);

        if (! $response->successful() || $response->json('Success') === false) {
            Log::error('iPaymu API error', [
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('iPaymu API error: '.$response->body());
        }

        return $response->json();
    }

    private function verifyCallbackSignature(array $payload, array $headers): void
    {
        $receivedSignature = $headers['x-signature'][0] ?? $headers['X-Signature'][0] ?? '';

        $normalized = $this->normalizeCallbackPayload($payload);
        ksort($normalized, SORT_STRING);
        $escapedJson = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $unescapedJson = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        if ($receivedSignature === '' || ! hash_equals(hash_hmac('sha256', $escapedJson, $this->va), $receivedSignature)
            && ! hash_equals(hash_hmac('sha256', $unescapedJson, $this->va), $receivedSignature)) {
            throw new \RuntimeException('Invalid iPaymu signature.');
        }
    }

    private function normalizeCallbackPayload(array $payload): array
    {
        $normalized = [];
        foreach ($payload as $key => $value) {
            if (in_array($key, ['trx_id', 'status_code', 'transaction_status_code', 'paid_off'], true)) {
                $normalized[$key] = is_numeric($value) ? (int) $value : $value;
            } elseif ($key === 'is_escrow') {
                $normalized[$key] = in_array($value, [true, 1, '1', 'true'], true);
            } elseif ($key === 'additional_info') {
                $normalized[$key] = $value === '[]' ? [] : $value;
            } else {
                $normalized[$key] = is_array($value)
                    ? json_encode($value)
                    : (string) $value;
            }
        }

        $normalized['additional_info'] ??= [];

        return $normalized;
    }

    private function mapStatus(array $payload): string
    {
        $statusCode = (int) ($payload['status_code'] ?? $payload['transaction_status_code'] ?? 0);
        $status = strtolower((string) ($payload['status'] ?? ''));

        return in_array($statusCode, [1, 6], true) || $status === 'berhasil'
            ? 'success'
            : (in_array($statusCode, [-2, 2, 3, 4, 5], true) || in_array($status, ['gagal', 'expired', 'cancelled', 'failed'], true) ? 'failed' : 'pending');
    }
}
