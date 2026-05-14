<?php

use App\Filament\Pages\Pos;
use App\Models\Toko;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('halaman POS cashier hanya memuat produk toko miliknya', function (): void {
    $admin = User::factory()->createOne([
        'role' => 'super_admin',
        'is_active' => true,
    ]);

    $tokoA = Toko::query()->create([
        'name' => 'Toko A',
        'created_by' => $admin->id,
    ]);

    $tokoB = Toko::query()->create([
        'name' => 'Toko B',
        'created_by' => $admin->id,
    ]);

    $categoryA = Category::query()->create([
        'toko_id' => $tokoA->id,
        'name' => 'Coffee',
    ]);

    $categoryB = Category::query()->create([
        'toko_id' => $tokoB->id,
        'name' => 'Tea',
    ]);

    Product::query()->create([
        'toko_id' => $tokoA->id,
        'category_id' => $categoryA->id,
        'name' => 'Espresso A',
        'price' => 15000,
        'stock' => 10,
        'is_active' => true,
    ]);

    Product::query()->create([
        'toko_id' => $tokoB->id,
        'category_id' => $categoryB->id,
        'name' => 'Tea B',
        'price' => 12000,
        'stock' => 10,
        'is_active' => true,
    ]);

    $kasir = User::factory()->createOne([
        'role' => 'kasir',
        'toko_id' => $tokoA->id,
        'is_active' => true,
    ]);

    $component = Livewire::actingAs($kasir)->test(Pos::class);

    /** @var array<int, array<string, mixed>> $products */
    $products = $component->instance()->products;

    expect($products)->toHaveCount(1)
        ->and($products[0]['name'])->toBe('Espresso A')
        ->and($products[0]['toko_id'])->toBe($tokoA->id);
}
);
