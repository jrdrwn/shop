<?php

use App\Filament\Pages\Cashier\Pos;
use App\Filament\Pages\PanduanPerangkat;
use App\Models\Category;
use App\Models\Product;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('panduan perangkat page renders interactive printer & scanner sections for kasir and owner', function (): void {
    $admin = User::factory()->createOne(['role' => 'super_admin', 'is_active' => true]);
    $toko = Toko::query()->create(['name' => 'Toko Panduan', 'created_by' => $admin->id]);
    $category = Category::query()->create(['toko_id' => $toko->id, 'name' => 'Snack']);

    Product::query()->create([
        'toko_id' => $toko->id,
        'category_id' => $category->id,
        'name' => 'Keripik Singkong',
        'price' => 10000,
        'stock' => 15,
        'sku' => '8991234567890',
        'is_active' => true,
    ]);

    foreach (['kasir', 'owner', 'gudang'] as $role) {
        $user = User::factory()->createOne([
            'role' => $role,
            'toko_id' => $toko->id,
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(PanduanPerangkat::class)
            ->assertSee('Pusat Panduan')
            ->assertSee('Penyambungan Printer Struk Thermal')
            ->assertSee('Tes & Kalibrasi Barcode Scanner')
            ->assertSee('EAN-13')
            ->assertSee('8991234567890');
    }
});

test('shortcut route /panduan-alat redirects each role to its panel guide page', function (): void {
    $kasir = User::factory()->createOne(['role' => 'kasir', 'is_active' => true]);
    actingAs($kasir);
    get('/panduan-alat')->assertRedirect('/cashier/panduan-perangkat');

    $owner = User::factory()->createOne(['role' => 'owner', 'is_active' => true]);
    actingAs($owner);
    get('/panduan-alat')->assertRedirect('/owner/panduan-perangkat');

    $gudang = User::factory()->createOne(['role' => 'gudang', 'is_active' => true]);
    actingAs($gudang);
    get('/panduan-alat')->assertRedirect('/warehouse/panduan-perangkat');
});

test('pos page displays quick-access link to Panduan Alat and loads pos-hardware.js', function (): void {
    $admin = User::factory()->createOne(['role' => 'super_admin', 'is_active' => true]);
    $toko = Toko::query()->create(['name' => 'Toko Kasir', 'created_by' => $admin->id]);
    $kasir = User::factory()->createOne([
        'role' => 'kasir',
        'toko_id' => $toko->id,
        'is_active' => true,
    ]);

    Livewire::actingAs($kasir)
        ->test(Pos::class)
        ->assertSeeHtml('/cashier/panduan-perangkat')
        ->assertSee('Panduan Alat')
        ->assertSeeHtml('js/pos-hardware.js');
});
