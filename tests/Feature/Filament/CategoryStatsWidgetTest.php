<?php

use App\Filament\Widgets\ResourceStats\CategoryStatsWidget;
use App\Models\Category;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

it('shows category statistics for the authenticated toko', function (): void {
    $owner = User::factory()->createOne([
        'role' => 'owner',
        'is_active' => true,
    ]);

    $toko = Toko::factory()->createOne([
        'created_by' => $owner->id,
    ]);

    $owner->update(['toko_id' => $toko->id]);

    Category::factory()->createOne([
        'toko_id' => $toko->id,
        'name' => 'Aktif 1',
        'is_active' => true,
    ]);

    Category::factory()->createOne([
        'toko_id' => $toko->id,
        'name' => 'Aktif 2',
        'is_active' => true,
    ]);

    Category::factory()->createOne([
        'toko_id' => $toko->id,
        'name' => 'Nonaktif 1',
        'is_active' => false,
    ]);

    Auth::login($owner);

    $widget = new class extends CategoryStatsWidget
    {
        public function exposeStats(): array
        {
            return $this->getStats();
        }
    };

    $stats = $widget->exposeStats();

    expect($stats)->toHaveCount(3);

    expect($stats[0]->getLabel())->toBe('Total Kategori')
        ->and($stats[0]->getValue())->toBe(3);

    expect($stats[1]->getLabel())->toBe('Kategori Aktif')
        ->and($stats[1]->getValue())->toBe(2);

    expect($stats[2]->getLabel())->toBe('Kategori Nonaktif')
        ->and($stats[2]->getValue())->toBe(1);
});
