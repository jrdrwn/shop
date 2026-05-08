<?php

namespace App\Filament\Resources\TransactionHistories;

use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Filament\Resources\TransactionHistories\Pages\CreateTransactionHistory;
use App\Filament\Resources\TransactionHistories\Pages\EditTransactionHistory;
use App\Filament\Resources\TransactionHistories\Pages\ListTransactionHistories;
use App\Filament\Resources\TransactionHistories\Schemas\TransactionHistoryForm;
use App\Filament\Resources\TransactionHistories\Tables\TransactionHistoriesTable;
use App\Models\TransactionHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransactionHistoryResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = TransactionHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $roleNavigationGroup = 'Laporan';

    protected static array $allowedRoles = ['admin', 'manager'];

    protected static ?string $recordTitleAttribute = 'action';

    public static function form(Schema $schema): Schema
    {
        return TransactionHistoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionHistoriesTable::configure($table);
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
            'index' => ListTransactionHistories::route('/'),
            'create' => CreateTransactionHistory::route('/create'),
            'edit' => EditTransactionHistory::route('/{record}/edit'),
        ];
    }
}
