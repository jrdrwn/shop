<?php

namespace App\Filament\Pages;

use App\Models\Product;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class Pos extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected string $view = 'filament.pages.pos';

    public array $products = [];

    public function mount(): void
    {
        $query = Product::query()
            ->where('is_active', true)
            ->select(['id', 'cafe_id', 'name', 'sku', 'price', 'stock', 'image_url']);

        $user = Auth::user();

        if ($user?->role === 'cashier' && filled($user->cafe_id)) {
            $query->where('cafe_id', $user->cafe_id);
        }

        $this->products = $query->orderBy('name')->get()->toArray();
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'cashier';
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Operasional';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
