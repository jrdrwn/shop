<?php

use App\Filament\Pages\Cashier\Pos;
use App\Models\Category;
use App\Models\Product;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('pos page renders searchable product data attributes', function () {
    $admin = User::factory()->createOne([
        'role' => 'super_admin',
        'is_active' => true,
    ]);

    $toko = Toko::query()->create([
        'name' => 'Tokatur',
        'created_by' => $admin->id,
    ]);

    $category = Category::query()->create([
        'toko_id' => $toko->id,
        'name' => 'Coffee',
    ]);

    Product::query()->create([
        'toko_id' => $toko->id,
        'category_id' => $category->id,
        'name' => 'Kopi Gula Aren',
        'price' => 18000,
        'stock' => 5,
        'sku' => 'SKU-1',
        'is_active' => true,
    ]);

    $kasir = User::factory()->createOne([
        'role' => 'kasir',
        'toko_id' => $toko->id,
        'is_active' => true,
    ]);

    Livewire::actingAs($kasir)
        ->test(Pos::class)
        ->assertSeeHtml('data-display-name="Kopi Gula Aren"')
        ->assertSeeHtml('data-name="kopi gula aren"')
        ->assertSeeHtml('data-price="18000"');
});
