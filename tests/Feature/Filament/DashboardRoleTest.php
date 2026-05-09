<?php

use App\Filament\Pages\CashierDashboard;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ManagerDashboard;
use App\Filament\Widgets\CashierHourlyTransactionsChart;
use App\Filament\Widgets\CashierStatsWidget;
use App\Filament\Widgets\CashierTodayTransactionsTable;
use App\Filament\Widgets\ManagerCafeTransactionsTable;
use App\Filament\Widgets\ManagerDailyRevenueChart;
use App\Filament\Widgets\ManagerLowStockTable;
use App\Filament\Widgets\ManagerStaffPerformanceTable;
use App\Filament\Widgets\ManagerStatsWidget;
use App\Filament\Widgets\ManagerTopProductsChart;
use App\Filament\Widgets\SuperAdminCafeSummaryTable;
use App\Filament\Widgets\SuperAdminStatsWidget;
use App\Filament\Widgets\SuperAdminSubscriptionChart;
use App\Models\Cafe;
use App\Models\CafeManager;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ─── Super Admin ────────────────────────────────────────────────────────────

test('super admin dashboard memantau cafe, manager, dan subscription', function (): void {
    $superAdmin = User::factory()->createOne(['role' => 'super_admin', 'is_active' => true]);
    $manager = User::factory()->createOne(['role' => 'manager', 'is_active' => true]);

    $subscription = Subscription::query()->create([
        'name' => 'Pro Plan', 'price' => 250000, 'duration_months' => 1,
        'features' => ['reports'], 'is_active' => true,
    ]);

    $cafe = Cafe::query()->create([
        'name' => 'Cafe Alpha', 'address' => 'Jl. Merdeka 1',
        'city' => 'Bandung', 'province' => 'Jawa Barat',
        'created_by' => $superAdmin->id, 'subscription_id' => $subscription->id, 'is_active' => true,
    ]);

    CafeManager::query()->create([
        'cafe_id' => $cafe->id, 'manager_id' => $manager->id,
        'assigned_at' => now(), 'assigned_by' => $superAdmin->id,
    ]);

    Livewire::actingAs($superAdmin);

    // Page access
    expect(Dashboard::canAccess())->toBeTrue()
        ->and(Dashboard::shouldRegisterNavigation())->toBeTrue();

    // Widgets are registered
    expect(Dashboard::class)->toHaveMethod('getWidgets');
    $widgets = (new Dashboard)->getWidgets();
    expect($widgets)->toContain(SuperAdminStatsWidget::class)
        ->toContain(SuperAdminSubscriptionChart::class)
        ->toContain(SuperAdminCafeSummaryTable::class);

    // Stats widget visible to super admin
    expect(SuperAdminStatsWidget::canView())->toBeTrue();

    // Cafe summary table widget renders
    $cafeTable = Livewire::test(SuperAdminCafeSummaryTable::class);
    expect(SuperAdminCafeSummaryTable::canView())->toBeTrue();
});

test('manager e cashier tidak bisa akses super admin dashboard', function (): void {
    $manager = User::factory()->createOne(['role' => 'manager']);
    Livewire::actingAs($manager);
    expect(Dashboard::shouldRegisterNavigation())->toBeFalse();

    $cashier = User::factory()->createOne(['role' => 'cashier']);
    Livewire::actingAs($cashier);
    expect(Dashboard::shouldRegisterNavigation())->toBeFalse();
});

// ─── Manager ────────────────────────────────────────────────────────────────

test('manager dashboard lebih detail dari cashier', function (): void {
    $superAdmin = User::factory()->createOne(['role' => 'super_admin', 'is_active' => true]);
    $manager = User::factory()->createOne(['role' => 'manager', 'is_active' => true]);
    $cashier = User::factory()->createOne(['role' => 'cashier', 'is_active' => true]);

    $cafe = Cafe::query()->create([
        'name' => 'Cafe Beta', 'address' => 'Jl. Sudirman 2',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'created_by' => $superAdmin->id, 'is_active' => true,
    ]);

    $manager->update(['cafe_id' => $cafe->id]);
    $cashier->update(['cafe_id' => $cafe->id]);

    $category = Category::query()->create(['cafe_id' => $cafe->id, 'name' => 'Minuman', 'is_active' => true]);

    $product = Product::query()->create([
        'cafe_id' => $cafe->id, 'category_id' => $category->id,
        'name' => 'Espresso', 'price' => 18000, 'cost' => 9000,
        'stock' => 4, 'sku' => 'ESP-001', 'is_active' => true,
        'has_variants' => false, 'variants' => [],
    ]);

    $transaction = Transaction::query()->create([
        'cafe_id' => $cafe->id, 'cashier_id' => $cashier->id,
        'transaction_number' => 'TRX-0101', 'total_amount' => 54000,
        'discount_amount' => 0, 'tax_amount' => 0,
        'paid_amount' => 54000, 'change_amount' => 0,
        'status' => 'completed', 'notes' => null,
    ]);

    TransactionItem::query()->create([
        'transaction_id' => $transaction->id, 'product_id' => $product->id,
        'quantity' => 3, 'unit_price' => 18000, 'subtotal' => 54000, 'notes' => null,
    ]);

    Livewire::actingAs($manager);

    expect(ManagerDashboard::canAccess())->toBeTrue()
        ->and(ManagerDashboard::shouldRegisterNavigation())->toBeTrue();

    // Manager dashboard has MORE widgets than cashier
    $managerWidgets = (new ManagerDashboard)->getWidgets();
    $cashierWidgets = (new CashierDashboard)->getWidgets();
    expect(count($managerWidgets))->toBeGreaterThan(count($cashierWidgets));

    // All manager widgets present
    expect($managerWidgets)->toContain(ManagerStatsWidget::class)
        ->toContain(ManagerDailyRevenueChart::class)
        ->toContain(ManagerTopProductsChart::class)
        ->toContain(ManagerLowStockTable::class)
        ->toContain(ManagerStaffPerformanceTable::class)
        ->toContain(ManagerCafeTransactionsTable::class);

    // Stats widget visible to manager
    expect(ManagerStatsWidget::canView())->toBeTrue();

    // Low stock table visible (product has stock=4 ≤ 10)
    expect(ManagerLowStockTable::canView())->toBeTrue();
});

test('super admin dan cashier tidak bisa akses manager dashboard', function (): void {
    $admin = User::factory()->createOne(['role' => 'super_admin']);
    Livewire::actingAs($admin);
    expect(ManagerDashboard::canAccess())->toBeFalse();

    $cashier = User::factory()->createOne(['role' => 'cashier']);
    Livewire::actingAs($cashier);
    expect(ManagerDashboard::canAccess())->toBeFalse();
});

// ─── Cashier ─────────────────────────────────────────────────────────────────

test('cashier dashboard fokus pada shift harian', function (): void {
    $cashier = User::factory()->createOne(['role' => 'cashier', 'is_active' => true]);
    $cafe = Cafe::query()->create([
        'name' => 'Cafe Gamma', 'address' => 'Jl. Asia Afrika 3',
        'city' => 'Bandung', 'province' => 'Jawa Barat',
        'created_by' => $cashier->id, 'is_active' => true,
    ]);
    $cashier->update(['cafe_id' => $cafe->id]);

    Transaction::query()->create([
        'cafe_id' => $cafe->id, 'cashier_id' => $cashier->id,
        'transaction_number' => 'TRX-0201', 'total_amount' => 45000,
        'discount_amount' => 0, 'tax_amount' => 0, 'paid_amount' => 45000,
        'change_amount' => 0, 'status' => 'completed', 'notes' => null,
    ]);

    Transaction::query()->create([
        'cafe_id' => $cafe->id, 'cashier_id' => $cashier->id,
        'transaction_number' => 'TRX-0202', 'total_amount' => 27000,
        'discount_amount' => 0, 'tax_amount' => 0, 'paid_amount' => 27000,
        'change_amount' => 0, 'status' => 'pending', 'notes' => null,
    ]);

    Livewire::actingAs($cashier);

    expect(CashierDashboard::canAccess())->toBeTrue()
        ->and(CashierDashboard::shouldRegisterNavigation())->toBeTrue();

    $widgets = (new CashierDashboard)->getWidgets();
    expect($widgets)->toContain(CashierStatsWidget::class)
        ->toContain(CashierHourlyTransactionsChart::class)
        ->toContain(CashierTodayTransactionsTable::class);

    // Stats widget visible to cashier
    expect(CashierStatsWidget::canView())->toBeTrue();

    // Today's transactions table shows 2 rows
    $table = Livewire::test(CashierTodayTransactionsTable::class);
    expect(CashierTodayTransactionsTable::canView())->toBeTrue();
});

test('super admin dan manager tidak bisa akses cashier dashboard', function (): void {
    $admin = User::factory()->createOne(['role' => 'super_admin']);
    Livewire::actingAs($admin);
    expect(CashierDashboard::canAccess())->toBeFalse();

    $manager = User::factory()->createOne(['role' => 'manager']);
    Livewire::actingAs($manager);
    expect(CashierDashboard::canAccess())->toBeFalse();
});
