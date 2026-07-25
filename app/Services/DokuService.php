<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Toko;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DokuService
{
    private string $clientId;

    private string $secretKey;

    private bool $isProduction;

    private string $baseUrl;

    public function __construct()
    {
        $this->clientId = (string) config('doku.client_id', '');
        $this->secretKey = (string) config('doku.secret_key', '');
        $this->isProduction = (bool) config('doku.is_production', false);
        $this->baseUrl = $this->isProduction
            ? 'https://api.doku.com'
            : 'https://api-sandbox.doku.com';
    }

    /**
     * Configure the service for a specific toko's Doku credentials.
     */
    public function forToko(Toko $toko): self
    {
        $clone = clone $this;

        if (filled($toko->doku_client_id)) {
            $clone->clientId = $toko->doku_client_id;
            $clone->secretKey = $toko->doku_secret_key ?? '';
            $clone->isProduction = $this->isProduction;
            $clone->baseUrl = $clone->isProduction
                ? 'https://api.doku.com'
                : 'https://api-sandbox.doku.com';
        }

        return $clone;
    }

    /**
     * Generate a QRIS code for a transaction via Doku Checkout Page link converted to QR code.
     */
    public function generateQris(Transaction $transaction): array
    {
        $toko = $transaction->toko;
        $orderId = $transaction->transaction_number;
        $amount = (int) $transaction->total_amount;

        $payload = [
            'order' => [
                'amount' => $amount,
                'invoice_number' => $orderId,
                'callback_url' => route('pos.finish').'?order_id='.$orderId,
                'auto_redirect' => true,
            ],
            'payment' => [
                'payment_due_date' => 60,
                'payment_method_types' => ['QRIS', 'EMVCO_QRIS'],
            ],
        ];

        $service = $this->forToko($toko);
        $checkoutResponse = $service->callApi('POST', '/checkout/v1/payment', $payload);

        $checkoutUrl = $checkoutResponse['response']['payment']['url'] ?? '';

        if (empty($checkoutUrl)) {
            throw new \RuntimeException('Gagal mendapatkan URL checkout Doku: '.json_encode($checkoutResponse));
        }

        // Generate QR code pointing to Doku payment page
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data='.urlencode($checkoutUrl);

        return [
            'transaction_id' => (string) ($checkoutResponse['uuid'] ?? $orderId),
            'qr_url' => $qrUrl,
            'checkout_url' => $checkoutUrl,
        ];
    }

    /**
     * Create a Checkout URL for subscription upgrade.
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
            'order' => [
                'amount' => (int) $subscription->price,
                'invoice_number' => $orderId,
                'callback_url' => route('subscription.finish').'?order_id='.$orderId,
                'auto_redirect' => true,
            ],
            'payment' => [
                'payment_due_date' => 60,
            ],
        ];

        try {
            $response = $this->callApi('POST', '/checkout/v1/payment', $payload);
            $checkoutUrl = $response['response']['payment']['url'] ?? '';

            if (empty($checkoutUrl)) {
                throw new \RuntimeException('Response Doku tidak memiliki payment URL.');
            }

            $payment->update([
                'metadata' => array_merge($payment->metadata ?? [], [
                    'checkout_url' => $checkoutUrl,
                    'snap_token' => $checkoutUrl, // map to existing property name for less code churn
                ]),
            ]);

            return $checkoutUrl;
        } catch (\Throwable $e) {
            $payment->update(['status' => 'failed', 'metadata' => ['error' => $e->getMessage()]]);
            throw new \RuntimeException('Gagal membuat transaksi Doku: '.$e->getMessage());
        }
    }

    /**
     * Handle Doku notification webhook.
     */
    public function handleNotification(array $payload, array $headers): SubscriptionPayment
    {
        // Reconstruct the signature to verify it
        $clientId = $headers['client-id'][0] ?? $headers['Client-Id'] ?? '';
        $requestId = $headers['request-id'][0] ?? $headers['Request-Id'] ?? '';
        $timestamp = $headers['request-timestamp'][0] ?? $headers['Request-Timestamp'] ?? '';
        $signatureHeader = $headers['signature'][0] ?? $headers['Signature'] ?? '';

        $rawBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $digest = base64_encode(hash('sha256', $rawBody, true));

        $rawSignature = "Client-Id:{$clientId}\n".
            "Request-Id:{$requestId}\n".
            "Request-Timestamp:{$timestamp}\n".
            "Request-Target:/notification\n". // Match the configured webhook path
            "Digest:{$digest}";

        $computedSignature = 'HMACSHA256='.base64_encode(hash_hmac('sha256', $rawSignature, $this->secretKey, true));

        // In sandbox or production, verify signature
        if (! hash_equals(str_replace('HMACSHA256=', '', $computedSignature), str_replace('HMACSHA256=', '', $signatureHeader))) {
            throw new \RuntimeException('Invalid Doku signature.');
        }

        $orderId = $payload['order']['invoice_number'] ?? '';
        $payment = SubscriptionPayment::where('order_id', $orderId)->firstOrFail();

        $transactionStatus = $payload['transaction']['status'] ?? '';
        $paymentType = $payload['transaction']['channel'] ?? null;
        $transactionId = $payload['transaction']['id'] ?? null;

        $status = match (strtoupper($transactionStatus)) {
            'SUCCESS' => 'success',
            'FAILED' => 'failed',
            default => 'pending',
        };

        $payment->update([
            'status' => $status,
            'payment_type' => $paymentType,
            'transaction_id' => $transactionId,
            'metadata' => array_merge($payment->metadata ?? [], $payload),
        ]);

        if ($status === 'success') {
            app(SubscriptionService::class)->activateSubscription(
                $payment->toko,
                $payment->subscription,
                $transactionId ?? $orderId
            );
        }

        return $payment;
    }

    public function checkStatus(string $orderId): array
    {
        try {
            $response = $this->callApi('GET', "/orders/v1/status/{$orderId}", []);
            $transactionStatus = $response['transaction']['status'] ?? '';

            if (strtolower($transactionStatus) === 'success') {
                return [
                    'status' => 'success',
                    'transaction_id' => $response['transaction']['original_request_id'] ?? $orderId,
                ];
            }

            return [
                'status' => 'pending',
                'transaction_id' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('Doku checkStatus API request failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);

            // Fallback to database check
            $payment = SubscriptionPayment::where('order_id', $orderId)->first();
            if ($payment) {
                return [
                    'status' => $payment->status,
                    'transaction_id' => $payment->transaction_id,
                ];
            }

            $transaction = Transaction::where('transaction_number', $orderId)->first();
            if ($transaction) {
                return [
                    'status' => $transaction->payment_status ?? 'pending',
                    'transaction_id' => $transaction->id,
                ];
            }

            return ['status' => 'pending'];
        }
    }

    /**
     * Helper to perform signed requests to Doku API.
     */
    private function callApi(string $method, string $targetPath, array $body): array
    {
        if (
            empty($this->clientId) ||
            empty($this->secretKey) ||
            str_starts_with($this->clientId, 'MCH-xxx') ||
            str_starts_with($this->secretKey, 'SK-xxx')
        ) {
            throw new \RuntimeException('Kredensial Doku (Client ID / Secret Key) belum dikonfigurasi dengan benar di file .env atau panel admin Toko Anda.');
        }

        $requestId = (string) Str::uuid();
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $isGet = strtoupper($method) === 'GET';

        $rawSignature = "Client-Id:{$this->clientId}\n".
            "Request-Id:{$requestId}\n".
            "Request-Timestamp:{$timestamp}\n".
            "Request-Target:{$targetPath}";

        $headers = [
            'Client-Id' => $this->clientId,
            'Request-Id' => $requestId,
            'Request-Timestamp' => $timestamp,
            'Content-Type' => 'application/json',
        ];

        if (! $isGet) {
            $jsonBody = json_encode($body, JSON_UNESCAPED_SLASHES);
            $digest = base64_encode(hash('sha256', $jsonBody, true));
            $rawSignature .= "\nDigest:{$digest}";
            $headers['Digest'] = $digest;
        }

        $signature = 'HMACSHA256='.base64_encode(hash_hmac('sha256', $rawSignature, $this->secretKey, true));
        $headers['Signature'] = $signature;

        Log::info('Sending request to Doku API', [
            'url' => $this->baseUrl.$targetPath,
            'headers' => $headers,
            'body' => $isGet ? null : $body,
        ]);

        $http = Http::timeout(10)
            ->when(app()->environment('local'), fn ($http) => $http->withoutVerifying())
            ->withHeaders($headers);

        if ($isGet) {
            $response = $http->send($method, $this->baseUrl.$targetPath);
        } else {
            $response = $http->send($method, $this->baseUrl.$targetPath, [
                'body' => $jsonBody,
            ]);
        }

        if (! $response->successful()) {
            Log::error('Doku API Error Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException("Doku API Error ({$response->status()}): ".$response->body());
        }

        return $response->json();
    }

    public function snapUrl(): string
    {
        return $this->isProduction
            ? 'https://jokul.doku.com/jokul-checkout-js/v1/jokul-checkout-1.0.0.js'
            : 'https://sandbox.doku.com/jokul-checkout-js/v1/jokul-checkout-1.0.0.js';
    }

    public function clientKey(): string
    {
        return $this->clientId;
    }
}
