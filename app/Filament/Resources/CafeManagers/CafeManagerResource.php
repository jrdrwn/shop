<?php

namespace App\Filament\Resources\CafeManagers;

use App\Filament\Resources\CafeManagers\Pages\CreateCafeManager;
use App\Filament\Resources\CafeManagers\Pages\EditCafeManager;
use App\Filament\Resources\CafeManagers\Pages\ListCafeManagers;
use App\Filament\Resources\CafeManagers\Schemas\CafeManagerForm;
use App\Filament\Resources\CafeManagers\Tables\CafeManagersTable;
use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Models\CafeManager;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CafeManagerResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = CafeManager::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'manager_id';

    protected static ?string $roleNavigationGroup = 'Manajemen Pengguna';

    protected static array $allowedRoles = ['admin', 'manager'];

    public static function form(Schema $schema): Schema
    {
        return CafeManagerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CafeManagersTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        $query = parent::getEloquentQuery();

        if ($user?->role === 'manager' && filled($user->cafe_id)) {
            return $query->where('cafe_id', $user->cafe_id);
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
            'index' => ListCafeManagers::route('/'),
            'create' => CreateCafeManager::route('/create'),
            'edit' => EditCafeManager::route('/{record}/edit'),
        ];
    }
}
