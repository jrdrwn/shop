<?php

use App\Filament\Pages\Pos;
use App\Models\Cafe;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('halaman POS cashier hanya memuat produk cafe miliknya', function (): void {
    $admin = User::factory()->createOne([
        'role' => 'super_admin',
        'is_active' => true,
    ]);

    $cafeA = Cafe::query()->create([
        'name' => 'Cafe A',
        'created_by' => $admin->id,
    ]);

    $cafeB = Cafe::query()->create([
        'name' => 'Cafe B',
        'created_by' => $admin->id,
    ]);

    $categoryA = Category::query()->create([
        'cafe_id' => $cafeA->id,
        'name' => 'Coffee',
    ]);

    $categoryB = Category::query()->create([
        'cafe_id' => $cafeB->id,
        'name' => 'Tea',
    ]);

    Product::query()->create([
        'cafe_id' => $cafeA->id,
        'category_id' => $categoryA->id,
        'name' => 'Espresso A',
        'price' => 15000,
        'stock' => 10,
        'is_active' => true,
    ]);

    Product::query()->create([
        'cafe_id' => $cafeB->id,
        'category_id' => $categoryB->id,
        'name' => 'Tea B',
        'price' => 12000,
        'stock' => 10,
        'is_active' => true,
    ]);

    $cashier = User::factory()->createOne([
        'role' => 'cashier',
        'cafe_id' => $cafeA->id,
        'is_active' => true,
    ]);

    $component = Livewire::actingAs($cashier)->test(Pos::class);

    /** @var array<int, array<string, mixed>> $products */
    $products = $component->instance()->products;

    expect($products)->toHaveCount(1)
        ->and($products[0]['name'])->toBe('Espresso A')
        ->and($products[0]['cafe_id'])->toBe($cafeA->id);
}
);
