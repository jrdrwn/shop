<?php

use App\Models\CashFlow;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Toko;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\IpaymuService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

function ipaymuSignature(array $payload, string $va): string
{
    $payload['trx_id'] = (int) $payload['trx_id'];
    $payload['status_code'] = (int) $payload['status_code'];
    $payload['additional_info'] = [];
    ksort($payload, SORT_STRING);

    return hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), $va);
}

test('toko iPaymu API key is encrypted and hidden from serialized data', function () {
    $toko = Toko::factory()->create(['ipaymu_api_key' => 'private-test-key']);

    expect($toko->ipaymu_api_key)->toBe('private-test-key')
        ->and(DB::table('tokos')->where('id', $toko->id)->value('ipaymu_api_key'))->not->toBe('private-test-key')
        ->and($toko->toArray())->not->toHaveKey('ipaymu_api_key');
});

test('signed direct QRIS request uses the toko credentials', function () {
    config()->set([
        'ipaymu.va' => 'global-va',
        'ipaymu.api_key' => 'global-key',
        'ipaymu.api_url' => 'https://sandbox.ipaymu.com/api/v2',
        'ipaymu.callback_base_url' => 'https://public.example.com',
    ]);

    Http::fake([
        'https://sandbox.ipaymu.com/api/v2/payment/direct' => Http::response([
            'Success' => true,
            'Data' => [
                'TransactionId' => 98765,
                'ReferenceId' => 'TRX-TEST-1',
                'Url' => 'https://sandbox.ipaymu.com/payment/98765',
            ],
        ]),
    ]);
    Http::preventStrayRequests();

    $toko = new Toko([
        'name' => 'Toko Test',
        'phone' => '08123456789',
        'email' => 'toko@example.com',
        'ipaymu_va' => '123456',
        'ipaymu_api_key' => 'test-api-key',
    ]);
    $transaction = new Transaction(['transaction_number' => 'TRX-TEST-1', 'total_amount' => 10000]);
    $transaction->setRelation('toko', $toko);

    $result = app(IpaymuService::class)->generateQris($transaction);

    expect($result['reference_id'])->toBe('TRX-TEST-1')
        ->and($result['transaction_id'])->toBe('98765')
        ->and($result['checkout_url'])->toBe('https://sandbox.ipaymu.com/payment/98765');

    Http::assertSent(fn (Request $request): bool => $request->header('va')[0] === '123456'
        && $request->header('signature')[0] === hash_hmac(
            'sha256',
            'POST:123456:'.hash('sha256', $request->body()).':test-api-key',
            'test-api-key'
        )
        && $request->data()['notifyUrl'] === 'https://public.example.com/cashier/pos/ipaymu-notification');
});

test('direct QRIS response can provide only a QR payload', function () {
    config()->set([
        'ipaymu.va' => 'test-va',
        'ipaymu.api_key' => 'test-key',
        'ipaymu.api_url' => 'https://sandbox.ipaymu.com/api/v2',
    ]);
    Http::fake([
        'https://sandbox.ipaymu.com/api/v2/payment/direct' => Http::response([
            'Success' => true,
            'Data' => [
                'TransactionId' => 98768,
                'ReferenceId' => 'TRX-QR-ONLY',
                'Qr' => '00020101021226600014ID.CO.QRIS.WWW',
            ],
        ]),
    ]);
    Http::preventStrayRequests();

    $toko = new Toko(['name' => 'Toko Test']);
    $transaction = new Transaction(['transaction_number' => 'TRX-QR-ONLY', 'total_amount' => 10000]);
    $transaction->setRelation('toko', $toko);

    $result = app(IpaymuService::class)->generateQris($transaction);

    expect($result['checkout_url'])->toBe('')
        ->and($result['qr_url'])->toStartWith('data:image/svg+xml;base64,');
});

test('signed subscription notification activates the plan only once', function () {
    config()->set(['ipaymu.va' => 'test-va', 'ipaymu.api_key' => 'test-key']);
    $toko = Toko::factory()->create();
    $subscription = Subscription::factory()->premium()->create();
    $payment = SubscriptionPayment::factory()->create([
        'toko_id' => $toko->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-TEST-1',
        'status' => 'pending',
    ]);
    $payload = ['reference_id' => 'SUB-TEST-1', 'status_code' => 1, 'trx_id' => 98765];

    $this->withHeaders(['X-Signature' => 'invalid'])
        ->postJson(route('subscription.ipaymu.notification'), $payload)
        ->assertBadRequest();
    expect($payment->fresh()->status)->toBe('pending');

    $signature = ipaymuSignature($payload, 'test-va');
    $this->withHeaders(['X-Signature' => $signature])
        ->postJson(route('subscription.ipaymu.notification'), $payload)
        ->assertSuccessful();
    $this->withHeaders(['X-Signature' => $signature])
        ->postJson(route('subscription.ipaymu.notification'), $payload)
        ->assertSuccessful();

    expect($payment->fresh()->status)->toBe('success')
        ->and($payment->fresh()->transaction_id)->toBe('98765')
        ->and($toko->fresh()->subscription_id)->toBe($subscription->id);
});

test('subscription return reconciles only a matching paid gateway transaction', function () {
    config()->set([
        'ipaymu.va' => 'test-va',
        'ipaymu.api_key' => 'test-key',
        'ipaymu.api_url' => 'https://sandbox.ipaymu.com/api/v2',
    ]);
    $toko = Toko::factory()->create();
    $subscription = Subscription::factory()->premium()->create();
    $payment = SubscriptionPayment::factory()->create([
        'toko_id' => $toko->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-RECONCILE-1',
        'amount' => 150000,
        'status' => 'pending',
        'metadata' => ['session_id' => 'session-1'],
    ]);

    Http::fake([
        'https://sandbox.ipaymu.com/api/v2/transaction' => Http::sequence()
            ->push(['Success' => true, 'Data' => [
                'ReferenceId' => 'OTHER-ORDER',
                'SubTotal' => 150000,
                'PaidStatus' => 'paid',
                'Status' => 1,
                'TransactionId' => 'gateway-1',
            ]])
            ->push(['Success' => true, 'Data' => [
                'ReferenceId' => 'SUB-RECONCILE-1',
                'SubTotal' => 150000,
                'PaidStatus' => 'paid',
                'Status' => 1,
                'TransactionId' => 'gateway-1',
            ]]),
    ]);
    Http::preventStrayRequests();

    expect(fn () => app(IpaymuService::class)->reconcileSubscriptionPayment($payment))
        ->toThrow(RuntimeException::class, 'iPaymu transaction does not match');
    expect($payment->fresh()->status)->toBe('pending')
        ->and($toko->fresh()->subscription_id)->toBeNull();

    expect(app(IpaymuService::class)->reconcileSubscriptionPayment($payment))->toBeTrue()
        ->and($payment->fresh()->status)->toBe('success')
        ->and($toko->fresh()->subscription_id)->toBe($subscription->id);
});

test('signed POS notification settles the payment and records income once', function () {
    config()->set(['ipaymu.va' => 'test-va', 'ipaymu.api_key' => 'test-key']);
    $toko = Toko::factory()->create();
    $cashier = User::factory()->create(['toko_id' => $toko->id, 'role' => 'kasir']);
    $transaction = Transaction::create([
        'toko_id' => $toko->id,
        'cashier_id' => $cashier->id,
        'transaction_number' => 'TRX-TEST-2',
        'total_amount' => 10000,
        'paid_amount' => 10000,
        'status' => 'pending',
    ]);
    $payment = Payment::create([
        'transaction_id' => $transaction->id,
        'payment_method_id' => $toko->paymentMethods()->where('type', 'qris')->firstOrFail()->id,
        'amount' => 10000,
        'status' => 'pending',
        'gateway_transaction_id' => '98766',
    ]);
    $payload = ['reference_id' => 'TRX-TEST-2', 'status_code' => 1, 'trx_id' => 98766];
    $signature = ipaymuSignature($payload, 'test-va');

    $this->actingAs($cashier)
        ->postJson(route('pos.cancel', $transaction->transaction_number))
        ->assertConflict();

    $this->withHeaders(['X-Signature' => $signature])
        ->postJson(route('pos.ipaymu.notification'), $payload)
        ->assertSuccessful();
    $this->withHeaders(['X-Signature' => $signature])
        ->postJson(route('pos.ipaymu.notification'), $payload)
        ->assertSuccessful();

    expect($transaction->fresh()->status)->toBe('completed')
        ->and($payment->fresh()->status)->toBe('success')
        ->and(CashFlow::where('reference_id', $transaction->id)->count())->toBe(1);
});

test('failed POS notification returns reserved stock once', function () {
    config()->set(['ipaymu.va' => 'test-va', 'ipaymu.api_key' => 'test-key']);
    $toko = Toko::factory()->create();
    $cashier = User::factory()->create(['toko_id' => $toko->id, 'role' => 'kasir']);
    $category = Category::factory()->create(['toko_id' => $toko->id]);
    $product = Product::factory()->create(['toko_id' => $toko->id, 'category_id' => $category->id, 'stock' => 4]);
    $transaction = Transaction::create([
        'toko_id' => $toko->id,
        'cashier_id' => $cashier->id,
        'transaction_number' => 'TRX-TEST-FAILED',
        'total_amount' => 10000,
        'paid_amount' => 10000,
        'status' => 'pending',
    ]);
    TransactionItem::create([
        'transaction_id' => $transaction->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 5000,
        'subtotal' => 10000,
    ]);
    Payment::create([
        'transaction_id' => $transaction->id,
        'payment_method_id' => $toko->paymentMethods()->where('type', 'qris')->firstOrFail()->id,
        'amount' => 10000,
        'status' => 'pending',
        'gateway_transaction_id' => '98767',
    ]);
    $payload = ['reference_id' => 'TRX-TEST-FAILED', 'status_code' => 4, 'trx_id' => 98767];
    $signature = ipaymuSignature($payload, 'test-va');

    $this->withHeaders(['X-Signature' => $signature])
        ->postJson(route('pos.ipaymu.notification'), $payload)
        ->assertSuccessful();
    $this->withHeaders(['X-Signature' => $signature])
        ->postJson(route('pos.ipaymu.notification'), $payload)
        ->assertSuccessful();

    expect($transaction->fresh()->status)->toBe('cancelled')
        ->and($product->fresh()->stock)->toBe(6);
});
