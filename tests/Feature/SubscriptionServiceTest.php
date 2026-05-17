<?php

use App\Enums\SubscriptionPlan;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Toko;
use App\Models\User;
use App\Services\SubscriptionService;

// ---------------------------------------------------------------------------
// Subscription::effectiveLimits()
// ---------------------------------------------------------------------------

test('effectiveLimits returns plan defaults when no custom limits are set', function () {
    $subscription = Subscription::factory()->free()->make();

    $limits = $subscription->effectiveLimits();

    expect($limits['max_products'])->toBe(10)
        ->and($limits['max_categories'])->toBe(3)
        ->and($limits['can_export_reports'])->toBeFalse()
        ->and($limits['can_use_variants'])->toBeFalse();
});

test('effectiveLimits merges custom limits over plan defaults', function () {
    $subscription = Subscription::factory()->free()->make([
        'limits' => ['max_products' => 25],
    ]);

    $limits = $subscription->effectiveLimits();

    expect($limits['max_products'])->toBe(25)   // overridden
        ->and($limits['max_categories'])->toBe(3); // still plan default
});

test('getLimit returns null for unlimited plans', function () {
    $subscription = Subscription::factory()->pro()->make();

    expect($subscription->getLimit('max_products'))->toBeNull()
        ->and($subscription->getLimit('max_categories'))->toBeNull();
});

test('hasFeature returns true for pro plan features', function () {
    $subscription = Subscription::factory()->pro()->make();

    expect($subscription->hasFeature('can_use_inventory'))->toBeTrue()
        ->and($subscription->hasFeature('can_export_reports'))->toBeTrue();
});

test('hasFeature returns false for free plan restricted features', function () {
    $subscription = Subscription::factory()->free()->make();

    expect($subscription->hasFeature('can_use_inventory'))->toBeFalse()
        ->and($subscription->hasFeature('can_export_reports'))->toBeFalse();
});

// ---------------------------------------------------------------------------
// SubscriptionService – product limits
// ---------------------------------------------------------------------------

test('canCreateProduct returns true when toko has no subscription', function () {
    $toko = Toko::factory()->create();

    $service = app(SubscriptionService::class);

    expect($service->canCreateProduct($toko))->toBeTrue();
});

test('canCreateProduct returns true when toko is under the product limit', function () {
    $subscription = Subscription::factory()->free()->create(); // max 10 products
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    $category = Category::factory()->create(['toko_id' => $toko->id]);
    // Add 9 products (under the limit)
    Product::factory()->count(9)->create(['toko_id' => $toko->id, 'category_id' => $category->id]);

    $service = app(SubscriptionService::class);

    expect($service->canCreateProduct($toko))->toBeTrue();
});

test('canCreateProduct returns false when toko has reached the product limit', function () {
    $subscription = Subscription::factory()->free()->create(); // max 10 products
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    $category = Category::factory()->create(['toko_id' => $toko->id]);
    // Fill up the limit
    Product::factory()->count(10)->create(['toko_id' => $toko->id, 'category_id' => $category->id]);

    $service = app(SubscriptionService::class);

    expect($service->canCreateProduct($toko))->toBeFalse();
});

test('canCreateProduct returns true for unlimited pro plan regardless of product count', function () {
    $subscription = Subscription::factory()->pro()->create(); // unlimited
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    $category = Category::factory()->create(['toko_id' => $toko->id]);
    Product::factory()->count(200)->create(['toko_id' => $toko->id, 'category_id' => $category->id]);

    $service = app(SubscriptionService::class);

    expect($service->canCreateProduct($toko))->toBeTrue();
});

test('remainingProducts returns null for unlimited plans', function () {
    $subscription = Subscription::factory()->pro()->create();
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    expect(app(SubscriptionService::class)->remainingProducts($toko))->toBeNull();
});

test('remainingProducts returns correct count for limited plan', function () {
    $subscription = Subscription::factory()->free()->create(); // max 10
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    $category = Category::factory()->create(['toko_id' => $toko->id]);
    Product::factory()->count(3)->create(['toko_id' => $toko->id, 'category_id' => $category->id]);

    expect(app(SubscriptionService::class)->remainingProducts($toko))->toBe(7);
});

// ---------------------------------------------------------------------------
// SubscriptionService – boolean feature checks
// ---------------------------------------------------------------------------

test('canUseInventory returns false for free plan', function () {
    $subscription = Subscription::factory()->free()->create();
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    expect(app(SubscriptionService::class)->canUseInventory($toko))->toBeFalse();
});

test('canUseInventory returns true for pro plan', function () {
    $subscription = Subscription::factory()->pro()->create();
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    expect(app(SubscriptionService::class)->canUseInventory($toko))->toBeTrue();
});

test('canUseDiscounts returns false for free plan', function () {
    $subscription = Subscription::factory()->free()->create();
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    expect(app(SubscriptionService::class)->canUseDiscounts($toko))->toBeFalse();
});

test('canUseDiscounts returns true for pro plan', function () {
    $subscription = Subscription::factory()->pro()->create();
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    expect(app(SubscriptionService::class)->canUseDiscounts($toko))->toBeTrue();
});

// ---------------------------------------------------------------------------
// SubscriptionPlan enum
// ---------------------------------------------------------------------------

test('pro plan has all features enabled', function () {
    $limits = SubscriptionPlan::Pro->defaultLimits();

    expect($limits['max_products'])->toBeNull()
        ->and($limits['can_export_reports'])->toBeTrue()
        ->and($limits['can_use_inventory'])->toBeTrue()
        ->and($limits['can_use_variants'])->toBeTrue()
        ->and($limits['can_use_discounts'])->toBeTrue();
});

test('free plan has restricted features', function () {
    $limits = SubscriptionPlan::Free->defaultLimits();

    expect($limits['max_products'])->toBe(10)
        ->and($limits['can_export_reports'])->toBeFalse()
        ->and($limits['can_use_inventory'])->toBeFalse();
});

// ---------------------------------------------------------------------------
// effectiveLimits null override (explicitly set to unlimited)
// ---------------------------------------------------------------------------

test('effectiveLimits allows overriding a plan limit to unlimited via null', function () {
    $subscription = Subscription::factory()->free()->make([
        'limits' => ['max_products' => null], // explicitly override to unlimited
    ]);

    expect($subscription->getLimit('max_products'))->toBeNull();
});

test('effectiveLimits preserves plan boolean false when not overridden', function () {
    $subscription = Subscription::factory()->free()->make([
        'limits' => ['max_products' => 5], // only override one key
    ]);

    // Boolean flags still from plan defaults
    expect($subscription->hasFeature('can_use_inventory'))->toBeFalse()
        ->and($subscription->getLimit('max_products'))->toBe(5);
});

// ---------------------------------------------------------------------------
// SubscriptionService — categories
// ---------------------------------------------------------------------------

test('canCreateCategory returns false when limit reached', function () {
    $subscription = Subscription::factory()->free()->create(); // max 3 categories
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    Category::factory()->count(3)->create(['toko_id' => $toko->id]);

    expect(app(SubscriptionService::class)->canCreateCategory($toko))->toBeFalse();
});

test('canCreateCategory returns true when under limit', function () {
    $subscription = Subscription::factory()->free()->create(); // max 3 categories
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    Category::factory()->count(2)->create(['toko_id' => $toko->id]);

    expect(app(SubscriptionService::class)->canCreateCategory($toko))->toBeTrue();
});

// ---------------------------------------------------------------------------
// SubscriptionService — payment methods
// ---------------------------------------------------------------------------

test('canAddPaymentMethod returns false when limit reached', function () {
    $subscription = Subscription::factory()->free()->create(); // max 2
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    PaymentMethod::factory()->count(2)->create(['toko_id' => $toko->id]);

    expect(app(SubscriptionService::class)->canAddPaymentMethod($toko))->toBeFalse();
});

test('canAddPaymentMethod returns true when unlimited on pro plan', function () {
    $subscription = Subscription::factory()->pro()->create();
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    PaymentMethod::factory()->count(50)->create(['toko_id' => $toko->id]);

    expect(app(SubscriptionService::class)->canAddPaymentMethod($toko))->toBeTrue();
});

// ---------------------------------------------------------------------------
// SubscriptionService — staff
// ---------------------------------------------------------------------------

test('canAddStaff counts toko users with Owner or kasir role', function () {
    $subscription = Subscription::factory()->free()->create(); // max_staff: 1
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    // Add 1 Owner (hitting the limit)
    User::factory()->create(['toko_id' => $toko->id, 'role' => 'owner']);

    expect(app(SubscriptionService::class)->canAddStaff($toko))->toBeFalse();
});

test('canAddStaff returns true when under the staff limit on pro plan', function () {
    $subscription = Subscription::factory()->pro()->create(); // unlimited staff
    $toko = Toko::factory()->create(['subscription_id' => $subscription->id]);

    User::factory()->count(10)->create(['toko_id' => $toko->id, 'role' => 'owner']);

    expect(app(SubscriptionService::class)->canAddStaff($toko))->toBeTrue();
});
