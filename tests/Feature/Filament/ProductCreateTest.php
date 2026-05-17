<?php

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Category;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('product create page fills toko_id from the authenticated Owner', function (): void {
    $Owner = User::factory()->createOne([
        'role' => 'owner',
        'is_active' => true,
    ]);

    $toko = Toko::query()->create([
        'name' => 'Toko Test',
        'created_by' => $Owner->id,
    ]);

    $Owner->update(['toko_id' => $toko->id]);

    $category = Category::query()->create([
        'toko_id' => $toko->id,
        'name' => 'Main',
    ]);

    actingAs($Owner);

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

    expect($data['toko_id'])->toBe($toko->id)
        ->and($data['variants'])->toBeNull();
});
