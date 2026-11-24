<?php

namespace App\Filament\Pages;

use App\Models\Product;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PanduanPerangkat extends Page
{
    protected static ?string $slug = 'panduan-perangkat';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Panduan Alat';

    protected static ?string $title = 'Panduan Alat Scan & Cetak Struk';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.panduan-perangkat';

    public string $tokoName = 'TOKO';

    /** @var array<string, string> */
    public array $productSkuMap = [];

    public function mount(): void
    {
        $user = Auth::user();
        $toko = $user?->toko;

        if ($toko) {
            $this->tokoName = $toko->name;
            $this->productSkuMap = Product::query()
                ->where('toko_id', $toko->id)
                ->whereNotNull('sku')
                ->where('sku', '!=', '')
                ->pluck('name', 'sku')
                ->toArray();
        }
    }

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
