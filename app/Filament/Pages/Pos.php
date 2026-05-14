<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Services\SubscriptionService;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Pos extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Kasir';

    protected static ?string $title = 'Kasir';

    protected string $view = 'filament.pages.pos';

    public array $products = [];

    public array $categories = [];

    public string $tokoName = 'TOKO';

    public ?string $tokoLogo = null;

    /** Tax percentage from the toko — read-only in POS */
    public int $taxPercentage = 0;

    /** Service charge percentage from the toko — read-only in POS */
    public int $serviceChargePercentage = 0;

    public function mount(): void
    {
        $user = Auth::user();

        $productQuery = Product::query()
            ->where('is_active', true)
            ->select(['id', 'toko_id', 'category_id', 'name', 'sku', 'price', 'discount_percentage', 'stock', 'image_url', 'has_variants', 'variants']);

        $categoryQuery = Category::query()
            ->select(['id', 'name']);

        if ($user?->role === 'kasir' && filled($user->toko_id)) {
            $productQuery->where('toko_id', $user->toko_id);
            $categoryQuery->where('toko_id', $user->toko_id);

            // Sync tax & service from the toko record
            $toko = $user->toko;
            if ($toko) {
                $this->taxPercentage = (int) $toko->tax_percentage;
                $this->serviceChargePercentage = (int) $toko->service_charge_percentage;
                $this->tokoName = $toko->name;
                $this->tokoLogo = $toko->logo_url;
            }
        }

        $products = $productQuery->orderBy('name', 'asc')->get();

        // Apply subscription feature restrictions on the data passed to the POS view
        if ($user?->role === 'kasir' && filled($user->toko_id)) {
            $toko = $user->toko;
            if ($toko) {
                $service = app(SubscriptionService::class);
                $canUseVariants = $service->canUseVariants($toko);
                $canUseDiscounts = $service->canUseDiscounts($toko);

                $products = $products->map(function ($product) use ($canUseVariants, $canUseDiscounts) {
                    $arr = $product->toArray();
                    // If variants are not allowed, treat all products as non-variant
                    if (! $canUseVariants) {
                        $arr['has_variants'] = false;
                        $arr['variants'] = null;
                    }
                    // If discounts are not allowed, zero out discount
                    if (! $canUseDiscounts) {
                        $arr['discount_percentage'] = 0;
                    }

                    return $arr;
                });
            }
        }

        $this->products = $products instanceof Collection
            ? $products->toArray()
            : $products;
        $this->categories = $categoryQuery->orderBy('name', 'asc')->get()->toArray();
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'kasir';
    }

    // public static function getNavigationGroup(): ?string
    // {
    //     return 'Operasional';
    // }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
