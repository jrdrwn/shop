<?php

namespace App\Filament\Resources\CashFlows;

use App\Filament\Resources\CashFlows\Pages\CreateCashFlow;
use App\Filament\Resources\CashFlows\Pages\EditCashFlow;
use App\Filament\Resources\CashFlows\Pages\ListCashFlows;
use App\Filament\Resources\CashFlows\Schemas\CashFlowForm;
use App\Filament\Resources\CashFlows\Tables\CashFlowsTable;
use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Models\CashFlow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CashFlowResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = CashFlow::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Arus Kas';

    protected static ?string $pluralModelLabel = 'Arus Kas';

    protected static ?string $modelLabel = 'Arus Kas';

    protected static ?string $roleNavigationGroup = 'Keuangan';

    protected static array $allowedRoles = ['owner'];

    public static function form(Schema $schema): Schema
    {
        return CashFlowForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashFlowsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        $query = parent::getEloquentQuery();

        if ($user?->role === 'owner' && filled($user->toko_id)) {
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
            'index' => ListCashFlows::route('/'),
            'create' => CreateCashFlow::route('/create'),
            'edit' => EditCashFlow::route('/{record}/edit'),
        ];
    }
}
