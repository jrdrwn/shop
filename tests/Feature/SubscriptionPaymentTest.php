<?php

use App\Enums\SubscriptionPlan;
use App\Models\Toko;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\SubscriptionService;

// ---------------------------------------------------------------------------
// SubscriptionPayment model
// ---------------------------------------------------------------------------

test('subscription payment can be created', function () {
    $toko = Toko::factory()->create();
    $subscription = Subscription::factory()->pro()->create();

    $payment = SubscriptionPayment::create([
        'toko_id' => $toko->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-TEST-123',
        'amount' => 150000,
        'status' => 'pending',
    ]);

    expect($payment)->toBeInstanceOf(SubscriptionPayment::class)
        ->and($payment->order_id)->toBe('SUB-TEST-123')
        ->and($payment->isPending())->toBeTrue()
        ->and($payment->isSuccess())->toBeFalse();
});

test('subscription payment status helpers work correctly', function () {
    $toko = Toko::factory()->create();
    $subscription = Subscription::factory()->pro()->create();

    $pending = SubscriptionPayment::create([
        'toko_id' => $toko->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-PENDING-1',
        'amount' => 150000,
        'status' => 'pending',
    ]);

    $success = SubscriptionPayment::create([
        'toko_id' => $toko->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-SUCCESS-1',
        'amount' => 150000,
        'status' => 'success',
    ]);

    $failed = SubscriptionPayment::create([
        'toko_id' => $toko->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-FAILED-1',
        'amount' => 150000,
        'status' => 'failed',
    ]);

    expect($pending->isPending())->toBeTrue()
        ->and($success->isSuccess())->toBeTrue()
        ->and($failed->isFailed())->toBeTrue();
});

// ---------------------------------------------------------------------------
// SubscriptionService – activate subscription
// ---------------------------------------------------------------------------

test('activate subscription updates toko subscription_id', function () {
    $toko = Toko::factory()->create();
    $proSubscription = Subscription::factory()->pro()->create();

    $service = app(SubscriptionService::class);
    $service->activateSubscription($toko, $proSubscription, 'trx-123');

    $toko->refresh();

    expect($toko->subscription_id)->toBe($proSubscription->id);
});

// ---------------------------------------------------------------------------
// Subscription plan enum – only Free and Pro
// ---------------------------------------------------------------------------

test('subscription plan enum has only free and pro cases', function () {
    $cases = SubscriptionPlan::cases();

    expect($cases)->toHaveCount(2)
        ->and(collect($cases)->pluck('value')->toArray())->toContain('free', 'pro');
});

test('free plan has correct default values', function () {
    $plan = SubscriptionPlan::Free;

    expect($plan->price())->toBe(0)
        ->and($plan->durationMonths())->toBe(0)
        ->and($plan->getLabel())->toBe('Free')
        ->and($plan->getColor())->toBe('gray');
});

test('pro plan has correct default values', function () {
    $plan = SubscriptionPlan::Pro;

    expect($plan->price())->toBe(150000)
        ->and($plan->durationMonths())->toBe(1)
        ->and($plan->getLabel())->toBe('Pro')
        ->and($plan->getColor())->toBe('warning');
});

test('free plan marketing features are correct', function () {
    $features = SubscriptionPlan::Free->marketingFeatures();

    expect($features)->toContain('10 Produk')
        ->and($features)->toContain('3 Kategori')
        ->and($features)->toContain('1 Staff')
        ->and($features)->toContain('2 Metode Pembayaran')
        ->and($features)->toContain('Laporan Dasar');
});

test('pro plan marketing features include all premium features', function () {
    $features = SubscriptionPlan::Pro->marketingFeatures();

    expect($features)->toContain('Produk Tidak Terbatas')
        ->and($features)->toContain('Kategori Tidak Terbatas')
        ->and($features)->toContain('Staff Tidak Terbatas')
        ->and($features)->toContain('Metode Pembayaran Tidak Terbatas')
        ->and($features)->toContain('Ekspor Laporan')
        ->and($features)->toContain('Manajemen Inventori')
        ->and($features)->toContain('Varian Produk')
        ->and($features)->toContain('Diskon Produk');
});

// ---------------------------------------------------------------------------
// SubscriptionPaymentController – unauthorized access
// ---------------------------------------------------------------------------

test('snap token endpoint requires authentication', function () {
    $response = $this->postJson(route('subscription.snap-token'), [
        'subscription_id' => 1,
    ]);

    $response->assertUnauthorized();
});

test('snap token endpoint requires Owner role', function () {
    $user = User::factory()->create(['role' => 'kasir']);
    $subscription = Subscription::factory()->pro()->create();

    $response = $this->actingAs($user)->postJson(route('subscription.snap-token'), [
        'subscription_id' => $subscription->id,
    ]);

    $response->assertForbidden();
});

// ---------------------------------------------------------------------------
// Midtrans notification webhook
// ---------------------------------------------------------------------------

test('midtrans notification endpoint is accessible without auth', function () {
    $response = $this->postJson(route('subscription.notification'), [
        'order_id' => 'SUB-TEST-123',
        'status_code' => '200',
        'gross_amount' => '150000.00',
        'signature_key' => 'invalid-signature',
    ]);

    // Should return 400 because signature is invalid, not 401
    $response->assertStatus(400);
});
