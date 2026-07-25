<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\DokuService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionPaymentController extends Controller
{
    public function __construct(
        private readonly DokuService $dokuService
    ) {}

    /**
     * Get Doku checkout URL for subscription upgrade.
     */
    public function getSnapToken(Request $request): JsonResponse
    {
        $request->validate([
            'subscription_id' => ['required', 'integer', 'exists:subscriptions,id'],
        ]);

        $user = Auth::user();

        if (! $user || ($user->role !== UserRole::Owner->value && $user->role !== 'owner') || ! $user->toko_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $toko = $user->toko;

        if (! $toko) {
            return response()->json(['message' => 'Toko not found.'], 404);
        }

        $subscription = Subscription::findOrFail($request->input('subscription_id'));

        if ($subscription->price <= 0) {
            // Free plan — activate directly without payment
            app(SubscriptionService::class)->activateSubscription($toko, $subscription, 'free-plan');

            return response()->json([
                'message' => 'Langganan Free berhasil diaktifkan.',
                'redirect' => route('filament.owner.pages.owner-panel-dashboard'),
            ]);
        }

        // Check for existing pending payments to avoid duplicates
        $existingPending = SubscriptionPayment::where('toko_id', $toko->id)
            ->where('subscription_id', $subscription->id)
            ->where('status', 'pending')
            ->where('created_at', '>', now()->subMinutes(15))
            ->first();

        if ($existingPending && isset($existingPending->metadata['snap_token'])) {
            return response()->json([
                'token' => $existingPending->metadata['snap_token'],
                'client_key' => $this->dokuService->clientKey(),
                'snap_url' => $this->dokuService->snapUrl(),
                'message' => 'Melanjutkan pembayaran yang tertunda.',
            ]);
        }

        $token = $this->dokuService->createSnapToken($toko, $subscription);

        return response()->json([
            'token' => $token,
            'client_key' => $this->dokuService->clientKey(),
            'snap_url' => $this->dokuService->snapUrl(),
        ]);
    }

    /**
     * Handle Doku notification webhook.
     */
    public function handleNotification(Request $request): JsonResponse
    {
        $payload = $request->all();
        $headers = $request->headers->all();

        Log::info('Doku notification received', $payload);

        try {
            $this->dokuService->handleNotification($payload, $headers);

            return response()->json(['message' => 'OK']);
        } catch (\Throwable $e) {
            Log::error('Doku notification failed', ['error' => $e->getMessage(), 'payload' => $payload]);

            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Payment finish callback (redirect after payment).
     */
    public function finish(Request $request): RedirectResponse
    {
        $orderId = $request->input('order_id');

        Log::info('Doku finish callback', ['order_id' => $orderId, 'query' => $request->all()]);

        if ($orderId) {
            $payment = SubscriptionPayment::where('order_id', $orderId)->first();

            if ($payment && $payment->status === 'pending') {
                try {
                    // Call Doku API check status to verify actual status
                    $statusResult = $this->dokuService->checkStatus($orderId);
                    Log::info('Doku finish status verification result', ['order_id' => $orderId, 'result' => $statusResult]);

                    if (($statusResult['status'] ?? '') === 'success') {
                        $payment->update([
                            'status' => 'success',
                            'transaction_id' => $statusResult['transaction_id'] ?? $orderId,
                            'settlement_time' => now(),
                        ]);

                        app(SubscriptionService::class)->activateSubscription(
                            $payment->toko,
                            $payment->subscription,
                            $statusResult['transaction_id'] ?? $orderId
                        );

                        return redirect()->route('filament.owner.pages.owner-panel-dashboard')
                            ->with('success', 'Pembayaran berhasil diverifikasi! Paket langganan Anda telah diperbarui.');
                    }
                } catch (\Throwable $e) {
                    Log::error('Doku finish verification failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);
                }
            }
        }

        return redirect()->route('filament.owner.pages.owner-panel-dashboard')
            ->with('success', 'Pembayaran sedang diproses. Status langganan akan diperbarui setelah verifikasi.');
    }

    /**
     * Payment error callback.
     */
    public function error(Request $request): RedirectResponse
    {
        $orderId = $request->input('order_id');

        Log::warning('Doku error callback', ['order_id' => $orderId]);

        return redirect()->route('filament.owner.pages.owner-panel-dashboard')
            ->with('error', 'Pembayaran gagal atau dibatalkan. Silakan coba lagi.');
    }
}
