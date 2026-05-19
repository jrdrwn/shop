<?php

use App\Filament\Pages\Cashier\Pos;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\InventoryLogs\InventoryLogResource;
use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\Tokos\TokoResource;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Role matrix:
 * - super_admin : platform management only (Toko, TokoOwner, Subscription)
 * - Owner     : all operational resources, scoped to their own toko
 * - cashier     : transactions only (for POS)
 */
$resourceRoleMatrix = [
    // ── Super Admin Only ────────────────────────────────────────────────────
    TokoResource::class => ['super_admin' => true,  'owner' => true,  'kasir' => false],
    SubscriptionResource::class => ['super_admin' => true,  'owner' => false, 'kasir' => false],

    // ── Owner Only (full ops, toko-scoped) ────────────────────────────────
    UserResource::class => ['super_admin' => false, 'owner' => true, 'kasir' => false],
    CategoryResource::class => ['super_admin' => false, 'owner' => true, 'kasir' => false],
    ProductResource::class => ['super_admin' => false, 'owner' => true, 'kasir' => false],
    PaymentMethodResource::class => ['super_admin' => false, 'owner' => true, 'kasir' => false],
    InventoryLogResource::class => ['super_admin' => false, 'owner' => true, 'kasir' => false],

    // ── Owner + Cashier ───────────────────────────────────────────────────
    TransactionResource::class => ['super_admin' => false, 'owner' => true, 'kasir' => true],
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

test('hanya kasir yang dapat mengakses halaman POS', function (): void {
    /** @var User $admin */
    $admin = User::factory()->createOne(['role' => 'super_admin']);
    actingAs($admin);
    expect(Pos::canAccess())->toBeFalse();

    /** @var User $Owner */
    $Owner = User::factory()->createOne(['role' => 'owner']);
    actingAs($Owner);
    expect(Pos::canAccess())->toBeFalse();

    /** @var User $kasir */
    $kasir = User::factory()->createOne(['role' => 'kasir']);
    actingAs($kasir);
    expect(Pos::canAccess())->toBeTrue()
        ->and(Pos::shouldRegisterNavigation())->toBeTrue();
});
