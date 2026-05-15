<?php

namespace App\Filament\Resources\StockMovements;

use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Filament\Resources\StockMovements\Pages\CreateStockMovement;
use App\Filament\Resources\StockMovements\Pages\EditStockMovement;
use App\Filament\Resources\StockMovements\Pages\ListStockMovements;
use App\Filament\Resources\StockMovements\Schemas\StockMovementForm;
use App\Filament\Resources\StockMovements\Tables\StockMovementsTable;
use App\Models\StockMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StockMovementResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = StockMovement::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';
 
    protected static ?string $navigationLabel = 'Riwayat Barang';
 
    protected static ?string $pluralModelLabel = 'Riwayat Barang';
 
    protected static ?string $modelLabel = 'Riwayat Barang';
 
    protected static ?string $roleNavigationGroup = 'Laporan';

    protected static array $allowedRoles = ['owner', 'gudang'];

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return StockMovementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockMovementsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }
 
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }
 
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
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
            'index' => ListStockMovements::route('/'),
        ];
    }
}
