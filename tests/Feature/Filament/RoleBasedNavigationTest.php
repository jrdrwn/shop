<?php

use App\Filament\Pages\Pos;
use App\Filament\Resources\Cafes\CafeResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\DailyReports\DailyReportResource;
use App\Filament\Resources\InventoryLogs\InventoryLogResource;
use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Settings\SettingResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\TransactionHistories\TransactionHistoryResource;
use App\Filament\Resources\TransactionItems\TransactionItemResource;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\UserActivityLogs\UserActivityLogResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

$resourceRoleMatrix = [
    UserResource::class => ['admin' => true, 'manager' => true, 'cashier' => false],
    'App\\Filament\\Resources\\CafeManagers\\CafeManagerResource' => ['admin' => true, 'manager' => true, 'cashier' => false],
    CafeResource::class => ['admin' => true, 'manager' => true, 'cashier' => false],
    CategoryResource::class => ['admin' => true, 'manager' => true, 'cashier' => false],
    ProductResource::class => ['admin' => true, 'manager' => true, 'cashier' => false],
    PaymentMethodResource::class => ['admin' => true, 'manager' => true, 'cashier' => false],
    TransactionResource::class => ['admin' => true, 'manager' => true, 'cashier' => true],
    PaymentResource::class => ['admin' => true, 'manager' => false, 'cashier' => false],
    TransactionItemResource::class => ['admin' => true, 'manager' => false, 'cashier' => false],
    DailyReportResource::class => ['admin' => true, 'manager' => true, 'cashier' => false],
    InventoryLogResource::class => ['admin' => true, 'manager' => true, 'cashier' => false],
    TransactionHistoryResource::class => ['admin' => true, 'manager' => true, 'cashier' => false],
    UserActivityLogResource::class => ['admin' => true, 'manager' => true, 'cashier' => false],
    SettingResource::class => ['admin' => true, 'manager' => false, 'cashier' => false],
    SubscriptionResource::class => ['admin' => true, 'manager' => false, 'cashier' => false],
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
    $admin = User::factory()->createOne(['role' => 'admin']);
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
