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
        $this->products = Product::where('is_active', true)->get()->toArray();
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
