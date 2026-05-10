<?php

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Cafe;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('product create page fills cafe_id from the authenticated manager', function (): void {
    $manager = User::factory()->createOne([
        'role' => 'manager',
        'is_active' => true,
    ]);

    $cafe = Cafe::query()->create([
        'name' => 'Cafe Test',
        'created_by' => $manager->id,
    ]);

    $manager->update(['cafe_id' => $cafe->id]);

    $category = Category::query()->create([
        'cafe_id' => $cafe->id,
        'name' => 'Main',
    ]);

    actingAs($manager);

    $page = new class extends CreateProduct
    {
        public function exposeMutateFormDataBeforeCreate(array $data): array
        {
            return $this->mutateFormDataBeforeCreate($data);
        }
    };

    $data = $page->exposeMutateFormDataBeforeCreate([
        'category_id' => $category->id,
        'name' => 'Nasgor',
        'price' => 23000,
        'stock' => 10,
        'has_variants' => false,
        'variants' => ['size' => ['Large']],
    ]);

    expect($data['cafe_id'])->toBe($cafe->id)
        ->and($data['variants'])->toBeNull();
});
