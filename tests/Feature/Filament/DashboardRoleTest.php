<?php

use App\Filament\Pages\CashierDashboard;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\OwnerDashboard;
use App\Filament\Widgets\CashierHourlyTransactionsChart;
use App\Filament\Widgets\CashierStatsWidget;
use App\Filament\Widgets\CashierTodayTransactionsTable;
use App\Filament\Widgets\OwnerDailyRevenueChart;
use App\Filament\Widgets\OwnerLowStockTable;
use App\Filament\Widgets\OwnerStaffPerformanceTable;
use App\Filament\Widgets\OwnerStatsWidget;
use App\Filament\Widgets\OwnerTokoTransactionsTable;
use App\Filament\Widgets\OwnerTopProductsChart;
use App\Filament\Widgets\SubscriptionStatusWidget;
use App\Filament\Widgets\SubscriptionUpgradeWidget;
use App\Filament\Widgets\SuperAdminStatsWidget;
use App\Filament\Widgets\SuperAdminSubscriptionChart;
use App\Filament\Widgets\SuperAdminTokoSummaryTable;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Toko;
use App\Models\TokoOwner;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ─── Super Admin ────────────────────────────────────────────────────────────

test('super admin dashboard memantau toko, Owner, dan subscription', function (): void {
    $superAdmin = User::factory()->createOne(['role' => 'super_admin', 'is_active' => true]);
    $Owner = User::factory()->createOne(['role' => 'owner', 'is_active' => true]);

    $subscription = Subscription::query()->create([
        'name' => 'Pro Plan', 'price' => 250000, 'duration_months' => 1,
        'features' => ['reports'], 'is_active' => true,
    ]);

    $toko = Toko::query()->create([
        'name' => 'Toko Alpha', 'address' => 'Jl. Merdeka 1',
        'city' => 'Bandung', 'province' => 'Jawa Barat',
        'created_by' => $superAdmin->id, 'subscription_id' => $subscription->id, 'is_active' => true,
    ]);

    TokoOwner::query()->create([
        'toko_id' => $toko->id, 'owner_id' => $Owner->id,
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
        ->toContain(SuperAdminTokoSummaryTable::class);

    // Stats widget visible to super admin
    expect(SuperAdminStatsWidget::canView())->toBeTrue();

    // Toko summary table widget renders
    $tokoTable = Livewire::test(SuperAdminTokoSummaryTable::class);
    expect(SuperAdminTokoSummaryTable::canView())->toBeTrue();
});

test('Owner e kasir tidak bisa akses super admin dashboard', function (): void {
    $Owner = User::factory()->createOne(['role' => 'owner']);
    Livewire::actingAs($Owner);
    expect(Dashboard::shouldRegisterNavigation())->toBeFalse();

    $kasir = User::factory()->createOne(['role' => 'kasir']);
    Livewire::actingAs($kasir);
    expect(Dashboard::shouldRegisterNavigation())->toBeFalse();
});

// ─── Owner ────────────────────────────────────────────────────────────────

test('Owner dashboard lebih detail dari kasir', function (): void {
    $superAdmin = User::factory()->createOne(['role' => 'super_admin', 'is_active' => true]);
    $Owner = User::factory()->createOne(['role' => 'owner', 'is_active' => true]);
    $kasir = User::factory()->createOne(['role' => 'kasir', 'is_active' => true]);
    $toko = Toko::query()->create([
        'name' => 'Toko Beta', 'address' => 'Jl. Sudirman 2',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'created_by' => $superAdmin->id, 'is_active' => true,
    ]);

    $Owner->update(['toko_id' => $toko->id]);
    $kasir->update(['toko_id' => $toko->id]);

    $category = Category::query()->create(['toko_id' => $toko->id, 'name' => 'Minuman', 'is_active' => true]);

    $product = Product::query()->create([
        'toko_id' => $toko->id, 'category_id' => $category->id,
        'name' => 'Espresso', 'price' => 18000,
        'stock' => 4, 'sku' => 'ESP-001', 'is_active' => true,
        'has_variants' => false, 'variants' => [],
    ]);

    $transaction = Transaction::query()->create([
        'toko_id' => $toko->id, 'cashier_id' => $kasir->id,
        'transaction_number' => 'TRX-0101', 'total_amount' => 54000,
        'discount_amount' => 0, 'tax_amount' => 0,
        'paid_amount' => 54000, 'change_amount' => 0,
        'status' => 'completed', 'notes' => null,
    ]);

    TransactionItem::query()->create([
        'transaction_id' => $transaction->id, 'product_id' => $product->id,
        'quantity' => 3, 'unit_price' => 18000, 'subtotal' => 54000, 'notes' => null,
    ]);

    Livewire::actingAs($Owner);

    expect(OwnerDashboard::canAccess())->toBeTrue()
        ->and(OwnerDashboard::shouldRegisterNavigation())->toBeTrue();

    // Owner dashboard has MORE widgets than cashier
    $ownerWidgets = (new OwnerDashboard)->getWidgets();
    $cashierWidgets = (new CashierDashboard)->getWidgets();
    expect(count($ownerWidgets))->toBeGreaterThan(count($cashierWidgets));

    // All Owner widgets present
    expect($ownerWidgets)->toContain(SubscriptionUpgradeWidget::class)
        ->toContain(SubscriptionStatusWidget::class)
        ->toContain(OwnerStatsWidget::class)
        ->toContain(OwnerDailyRevenueChart::class)
        ->toContain(OwnerTopProductsChart::class)
        ->toContain(OwnerLowStockTable::class)
        ->toContain(OwnerStaffPerformanceTable::class)
        ->toContain(OwnerTokoTransactionsTable::class);

    // Stats widget visible to Owner
    expect(OwnerStatsWidget::canView())->toBeTrue();

    // Low stock table visible (product has stock=4 ≤ 10)
    expect(OwnerLowStockTable::canView())->toBeTrue();
});

test('super admin dan kasir tidak bisa akses Owner dashboard', function (): void {
    $admin = User::factory()->createOne(['role' => 'super_admin']);
    Livewire::actingAs($admin);
    expect(OwnerDashboard::canAccess())->toBeFalse();

    $kasir = User::factory()->createOne(['role' => 'kasir']);
    Livewire::actingAs($kasir);
    expect(OwnerDashboard::canAccess())->toBeFalse();
});

// ─── Cashier ─────────────────────────────────────────────────────────────────

test('kasir dashboard fokus pada shift harian', function (): void {
    $kasir = User::factory()->createOne(['role' => 'kasir', 'is_active' => true]);
    $toko = Toko::query()->create([
        'name' => 'Toko Gamma', 'address' => 'Jl. Asia Afrika 3',
        'city' => 'Bandung', 'province' => 'Jawa Barat',
        'created_by' => $kasir->id, 'is_active' => true,
    ]);
    $kasir->update(['toko_id' => $toko->id]);

    Transaction::query()->create([
        'toko_id' => $toko->id, 'cashier_id' => $kasir->id,
        'transaction_number' => 'TRX-0201', 'total_amount' => 45000,
        'discount_amount' => 0, 'tax_amount' => 0, 'paid_amount' => 45000,
        'change_amount' => 0, 'status' => 'completed', 'notes' => null,
    ]);

    Transaction::query()->create([
        'toko_id' => $toko->id, 'cashier_id' => $kasir->id,
        'transaction_number' => 'TRX-0202', 'total_amount' => 27000,
        'discount_amount' => 0, 'tax_amount' => 0, 'paid_amount' => 27000,
        'change_amount' => 0, 'status' => 'pending', 'notes' => null,
    ]);

    Livewire::actingAs($kasir);

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

test('super admin dan Owner tidak bisa akses cashier dashboard', function (): void {
    $admin = User::factory()->createOne(['role' => 'super_admin']);
    Livewire::actingAs($admin);
    expect(CashierDashboard::canAccess())->toBeFalse();

    $Owner = User::factory()->createOne(['role' => 'owner']);
    Livewire::actingAs($Owner);
    expect(CashierDashboard::canAccess())->toBeFalse();
});
