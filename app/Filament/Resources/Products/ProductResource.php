<?php

namespace App\Filament\Resources\Products;

use App\Filament\Concerns\ResolvesSubscription;
use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductResource extends Resource
{
    use HasRoleNavigation, ResolvesSubscription;

    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Produk';

    protected static ?string $pluralModelLabel = 'Produk';

    protected static ?string $modelLabel = 'Produk';

    protected static ?string $roleNavigationGroup = 'Master Data';

    protected static array $allowedRoles = ['owner', 'gudang'];

    protected static ?string $recordTitleAttribute = 'name';
    public static function getNavigationGroup(): ?string
    {

        if (Auth::user()?->role === 'owner') {
            return static::$roleNavigationGroup;
        }

        return null;
    }
    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        $query = parent::getEloquentQuery();

        if (in_array($user?->role, ['owner', 'gudang']) && filled($user->toko_id)) {
            return $query->where('toko_id', $user->toko_id);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
