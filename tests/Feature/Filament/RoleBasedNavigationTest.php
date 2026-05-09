<?php

use App\Filament\Pages\Pos;
use App\Filament\Resources\Cafes\CafeResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\InventoryLogs\InventoryLogResource;
use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Role matrix:
 * - super_admin : platform management only (Cafe, CafeManager, Subscription)
 * - manager     : all operational resources, scoped to their own cafe
 * - cashier     : transactions only (for POS)
 */
$resourceRoleMatrix = [
    // ── Super Admin Only ────────────────────────────────────────────────────
    CafeResource::class => ['super_admin' => true,  'manager' => true,  'cashier' => false],
    SubscriptionResource::class => ['super_admin' => true,  'manager' => false, 'cashier' => false],

    // ── Manager Only (full ops, cafe-scoped) ────────────────────────────────
    UserResource::class => ['super_admin' => false, 'manager' => true, 'cashier' => false],
    CategoryResource::class => ['super_admin' => false, 'manager' => true, 'cashier' => false],
    ProductResource::class => ['super_admin' => false, 'manager' => true, 'cashier' => false],
    PaymentMethodResource::class => ['super_admin' => false, 'manager' => true, 'cashier' => false],
    InventoryLogResource::class => ['super_admin' => false, 'manager' => true, 'cashier' => false],

    // ── Manager + Cashier ───────────────────────────────────────────────────
    TransactionResource::class => ['super_admin' => false, 'manager' => true, 'cashier' => true],
];

foreach ($resourceRoleMatrix as $resourceClass => $accessMap) {
    foreach ($accessMap as $role => $canAccess) {
        test("{$role} akses {$resourceClass} sesuai role matrix", function () use ($resourceClass, $role, $canAccess): void {
            /** @var User $user */
            $user = User::factory()->createOne(['role' => $role]);
            actingAs($user);

            expect($resourceClass::canAccess())->toBe($canAccess)
                ->and($resourceClass::shouldRegisterNavigation())->toBe($canAccess);
        });
    }
}

test('guest tidak bisa akses resource', function () use ($resourceRoleMatrix): void {
    foreach (array_keys($resourceRoleMatrix) as $resourceClass) {
        expect($resourceClass::canAccess())->toBeFalse()
            ->and($resourceClass::shouldRegisterNavigation())->toBeFalse();
    }
});

test('hanya cashier yang dapat mengakses halaman POS', function (): void {
    /** @var User $admin */
    $admin = User::factory()->createOne(['role' => 'super_admin']);
    actingAs($admin);
    expect(Pos::canAccess())->toBeFalse();

    /** @var User $manager */
    $manager = User::factory()->createOne(['role' => 'manager']);
    actingAs($manager);
    expect(Pos::canAccess())->toBeFalse();

    /** @var User $cashier */
    $cashier = User::factory()->createOne(['role' => 'cashier']);
    actingAs($cashier);
    expect(Pos::canAccess())->toBeTrue()
        ->and(Pos::shouldRegisterNavigation())->toBeTrue();
});
