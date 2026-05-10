<?php

use App\Enums\SubscriptionPlan;
use App\Models\Cafe;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Subscription;
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

test('canCreateProduct returns true when cafe has no subscription', function () {
    $cafe = Cafe::factory()->create();

    $service = app(SubscriptionService::class);

    expect($service->canCreateProduct($cafe))->toBeTrue();
});

test('canCreateProduct returns true when cafe is under the product limit', function () {
    $subscription = Subscription::factory()->free()->create(); // max 10 products
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    $category = Category::factory()->create(['cafe_id' => $cafe->id]);
    // Add 9 products (under the limit)
    Product::factory()->count(9)->create(['cafe_id' => $cafe->id, 'category_id' => $category->id]);

    $service = app(SubscriptionService::class);

    expect($service->canCreateProduct($cafe))->toBeTrue();
});

test('canCreateProduct returns false when cafe has reached the product limit', function () {
    $subscription = Subscription::factory()->free()->create(); // max 10 products
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    $category = Category::factory()->create(['cafe_id' => $cafe->id]);
    // Fill up the limit
    Product::factory()->count(10)->create(['cafe_id' => $cafe->id, 'category_id' => $category->id]);

    $service = app(SubscriptionService::class);

    expect($service->canCreateProduct($cafe))->toBeFalse();
});

test('canCreateProduct returns true for unlimited pro plan regardless of product count', function () {
    $subscription = Subscription::factory()->pro()->create(); // unlimited
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    $category = Category::factory()->create(['cafe_id' => $cafe->id]);
    Product::factory()->count(200)->create(['cafe_id' => $cafe->id, 'category_id' => $category->id]);

    $service = app(SubscriptionService::class);

    expect($service->canCreateProduct($cafe))->toBeTrue();
});

test('remainingProducts returns null for unlimited plans', function () {
    $subscription = Subscription::factory()->pro()->create();
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    expect(app(SubscriptionService::class)->remainingProducts($cafe))->toBeNull();
});

test('remainingProducts returns correct count for limited plan', function () {
    $subscription = Subscription::factory()->free()->create(); // max 10
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    $category = Category::factory()->create(['cafe_id' => $cafe->id]);
    Product::factory()->count(3)->create(['cafe_id' => $cafe->id, 'category_id' => $category->id]);

    expect(app(SubscriptionService::class)->remainingProducts($cafe))->toBe(7);
});

// ---------------------------------------------------------------------------
// SubscriptionService – boolean feature checks
// ---------------------------------------------------------------------------

test('canUseInventory returns false for free plan', function () {
    $subscription = Subscription::factory()->free()->create();
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    expect(app(SubscriptionService::class)->canUseInventory($cafe))->toBeFalse();
});

test('canUseInventory returns true for pro plan', function () {
    $subscription = Subscription::factory()->pro()->create();
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    expect(app(SubscriptionService::class)->canUseInventory($cafe))->toBeTrue();
});

test('canUseDiscounts returns false for free plan', function () {
    $subscription = Subscription::factory()->free()->create();
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    expect(app(SubscriptionService::class)->canUseDiscounts($cafe))->toBeFalse();
});

test('canUseDiscounts returns true for pro plan', function () {
    $subscription = Subscription::factory()->pro()->create();
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    expect(app(SubscriptionService::class)->canUseDiscounts($cafe))->toBeTrue();
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
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    Category::factory()->count(3)->create(['cafe_id' => $cafe->id]);

    expect(app(SubscriptionService::class)->canCreateCategory($cafe))->toBeFalse();
});

test('canCreateCategory returns true when under limit', function () {
    $subscription = Subscription::factory()->free()->create(); // max 3 categories
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    Category::factory()->count(2)->create(['cafe_id' => $cafe->id]);

    expect(app(SubscriptionService::class)->canCreateCategory($cafe))->toBeTrue();
});

// ---------------------------------------------------------------------------
// SubscriptionService — payment methods
// ---------------------------------------------------------------------------

test('canAddPaymentMethod returns false when limit reached', function () {
    $subscription = Subscription::factory()->free()->create(); // max 2
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    PaymentMethod::factory()->count(2)->create(['cafe_id' => $cafe->id]);

    expect(app(SubscriptionService::class)->canAddPaymentMethod($cafe))->toBeFalse();
});

test('canAddPaymentMethod returns true when unlimited on pro plan', function () {
    $subscription = Subscription::factory()->pro()->create();
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    PaymentMethod::factory()->count(50)->create(['cafe_id' => $cafe->id]);

    expect(app(SubscriptionService::class)->canAddPaymentMethod($cafe))->toBeTrue();
});

// ---------------------------------------------------------------------------
// SubscriptionService — staff
// ---------------------------------------------------------------------------

test('canAddStaff counts cafe users with manager or cashier role', function () {
    $subscription = Subscription::factory()->free()->create(); // max_staff: 1
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    // Add 1 manager (hitting the limit)
    User::factory()->create(['cafe_id' => $cafe->id, 'role' => 'manager']);

    expect(app(SubscriptionService::class)->canAddStaff($cafe))->toBeFalse();
});

test('canAddStaff returns true when under the staff limit on pro plan', function () {
    $subscription = Subscription::factory()->pro()->create(); // unlimited staff
    $cafe = Cafe::factory()->create(['subscription_id' => $subscription->id]);

    User::factory()->count(10)->create(['cafe_id' => $cafe->id, 'role' => 'manager']);

    expect(app(SubscriptionService::class)->canAddStaff($cafe))->toBeTrue();
});
