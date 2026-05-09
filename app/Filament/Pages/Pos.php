<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Pos extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected string $view = 'filament.pages.pos';

    public array $products = [];

    public array $categories = [];

    /** Tax percentage from the cafe — read-only in POS */
    public int $taxPercentage = 0;

    /** Service charge percentage from the cafe — read-only in POS */
    public int $serviceChargePercentage = 0;

    public function mount(): void
    {
        $user = Auth::user();

        $productQuery = Product::query()
            ->where('is_active', true)
            ->select(['id', 'cafe_id', 'category_id', 'name', 'sku', 'price', 'discount_percentage', 'stock', 'image_url', 'has_variants', 'variants']);

        $categoryQuery = Category::query()
            ->select(['id', 'name']);

        if ($user?->role === 'cashier' && filled($user->cafe_id)) {
            $productQuery->where('cafe_id', $user->cafe_id);
            $categoryQuery->where('cafe_id', $user->cafe_id);

            // Sync tax & service from the cafe record
            $cafe = $user->cafe;
            if ($cafe) {
                $this->taxPercentage = (int) $cafe->tax_percentage;
                $this->serviceChargePercentage = (int) $cafe->service_charge_percentage;
            }
        }

        $this->products = $productQuery->orderBy('name', 'asc')->get()->toArray();
        $this->categories = $categoryQuery->orderBy('name', 'asc')->get()->toArray();
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'cashier';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Operasional';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
