<?php

use App\Filament\Resources\CashFlows\Pages\CreateCashFlow;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('cash flow create page fills toko_id and created_by from the authenticated Owner', function (): void {
    $Owner = User::factory()->createOne([
        'role' => 'owner',
        'is_active' => true,
    ]);

    $toko = Toko::query()->create([
        'name' => 'Toko Test',
        'created_by' => $Owner->id,
    ]);

    $Owner->update(['toko_id' => $toko->id]);

    actingAs($Owner);

    $page = new class extends CreateCashFlow
    {
        public function exposeMutateFormDataBeforeCreate(array $data): array
        {
            return $this->mutateFormDataBeforeCreate($data);
        }
    };

    $data = $page->exposeMutateFormDataBeforeCreate([
        'type' => 'expense',
        'category' => 'supplies',
        'amount' => 50000,
        'description' => 'Beli kopi',
    ]);

    expect($data['toko_id'])->toBe($toko->id)
        ->and($data['created_by'])->toBe($Owner->id);
});
