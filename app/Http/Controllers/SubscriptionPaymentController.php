<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\IpaymuService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionPaymentController extends Controller
{
    public function __construct(
        private readonly IpaymuService $ipaymuService
    ) {}

    /**
     * Get the hosted iPaymu payment URL for subscription upgrade.
     */
    public function getSnapToken(Request $request): JsonResponse
    {
        $request->validate([
            'subscription_id' => ['required', 'integer', 'exists:subscriptions,id'],
        ]);

        $user = Auth::user();

        if (! $user || $user->role !== 'owner' || ! $user->toko_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $toko = $user->toko;

        if (! $toko) {
            return response()->json(['message' => 'Toko not found.'], 404);
        }

        $subscription = Subscription::findOrFail($request->input('subscription_id'));

        // Security: Ensure plan is active and available
        if (! $subscription->is_active) {
            return response()->json(['message' => 'Paket langganan ini sedang tidak tersedia.'], 422);
        }

        // Security: Prevent downgrade to free plan if they already have an active paid plan (optional business rule)
        // Or at least prevent re-activating the exact same plan if it's still far from expiry
        if ($subscription->price <= 0) {
            if ((int) $toko->subscription_id === (int) $subscription->id) {
                return response()->json(['message' => 'Anda sudah menggunakan paket ini.'], 422);
            }

            // Direct activation for free plans
            app(SubscriptionService::class)->activateSubscription($toko, $subscription, 'free-plan-'.time());

            return response()->json([
                'message' => 'Langganan berhasil diaktifkan.',
                'redirect' => route('filament.owner.pages.owner-panel-dashboard'),
            ]);
        }

        // Security: Check for existing pending payments for this toko to avoid duplicates
        $existingPending = SubscriptionPayment::where('toko_id', $toko->id)
            ->where('subscription_id', $subscription->id)
            ->where('status', 'pending')
            ->where('created_at', '>', now()->subMinutes(15))
            ->first();

        if ($existingPending && isset($existingPending->metadata['checkout_url'])) {
            return response()->json([
                'token' => $existingPending->metadata['checkout_url'],
                'message' => 'Melanjutkan pembayaran yang tertunda.',
            ]);
        }

        $paymentUrl = $this->ipaymuService->createPaymentUrl($toko, $subscription);

        return response()->json([
            'token' => $paymentUrl,
        ]);
    }

    /**
     * Handle iPaymu notification webhook.
     */
    public function handleNotification(Request $request): JsonResponse
    {
        $content = $request->getContent();
        $decoded = str_starts_with(ltrim($content), '{') ? json_decode($content, true) : null;
        $payload = is_array($decoded) ? $decoded : $request->all();

        $headers = $request->headers->all();

        Log::info('iPaymu subscription notification received', [
            'content_type' => $request->header('Content-Type'),
            'raw_body_length' => strlen($content),
            'payload_keys' => array_keys($payload),
        ]);

        try {
            $this->ipaymuService->handleNotification($payload, $headers);

            return response()->json(['message' => 'OK'], 200);
        } catch (\Throwable $e) {
            Log::error('iPaymu subscription notification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload_size' => count($payload),
            ]);

            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Payment finish callback (redirect after payment).
     */
    public function finish(Request $request): RedirectResponse
    {
        $orderId = $request->input('order_id');

        Log::info('iPaymu finish callback', ['order_id' => $orderId, 'query' => $request->all()]);

        $payment = $orderId ? SubscriptionPayment::where('order_id', $orderId)->first() : null;

        if ($payment?->status === 'pending') {
            try {
                $this->ipaymuService->reconcileSubscriptionPayment($payment);
                $payment->refresh();
            } catch (\Throwable $exception) {
                Log::warning('iPaymu return reconciliation failed', [
                    'order_id' => $orderId,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if ($payment?->status === 'success') {
            return redirect()->route('filament.owner.pages.owner-panel-dashboard')
                ->with('success', 'Pembayaran berhasil. Paket langganan Anda telah diperbarui.');
        }

        if ($payment?->status === 'failed') {
            return redirect()->route('filament.owner.pages.owner-panel-dashboard')
                ->with('error', 'Pembayaran gagal. Silakan coba lagi.');
        }

        return redirect()->route('filament.owner.pages.owner-panel-dashboard')
            ->with('info', 'Pembayaran menunggu notifikasi iPaymu. Status langganan akan diperbarui otomatis.');
    }

    /**
     * Payment error callback.
     */
    public function error(Request $request): RedirectResponse
    {
        $orderId = $request->input('order_id');

        Log::warning('iPaymu error callback', ['order_id' => $orderId]);

        $payment = $orderId ? SubscriptionPayment::where('order_id', $orderId)->first() : null;

        if ($payment?->status === 'failed') {
            return redirect()->route('filament.owner.pages.owner-panel-dashboard')
                ->with('error', 'Pembayaran gagal. Silakan coba lagi.');
        }

        return redirect()->route('filament.owner.pages.owner-panel-dashboard')
            ->with('info', 'Status pembayaran menunggu notifikasi iPaymu.');
    }
}
