<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Transactions\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Transactions\Schemas\TransactionForm;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Models\Transaction;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransactionResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Transaksi';

    protected static ?string $pluralModelLabel = 'Transaksi';

    protected static ?string $modelLabel = 'Transaksi';

    protected static ?string $roleNavigationGroup = 'Laporan';

    protected static array $allowedRoles = ['owner', 'cashier'];

    protected static ?string $recordTitleAttribute = 'transaction_number';

    public static function getNavigationGroup(): ?string
    {

        if (Auth::user()?->role === 'owner') {
            return static::$roleNavigationGroup;
        }

        return null;
    }

    public static function form(Schema $schema): Schema
    {
        return TransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionsTable::configure($table->recordActions([
            Action::make('completed')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->hiddenLabel()
                ->hidden(fn ($record) => $record?->status === 'completed')
                ->action(fn ($record) => $record->update(['status' => 'completed'])),
            Action::make('cancel')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->hiddenLabel()
                ->requiresConfirmation()
                ->hidden(fn ($record) => in_array($record?->status, ['completed', 'cancelled'] ?? []))
                ->action(fn ($record) => $record->update(['status' => 'cancelled'])),
            Action::make('print')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->hiddenLabel()
                ->url(fn ($record) => route('transactions.receipt', $record))
                ->openUrlInNewTab(),
        ]));
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        $query = parent::getEloquentQuery();

        if ($user?->role === 'owner' && filled($user->toko_id)) {
            return $query->where('toko_id', $user->toko_id);
        }

        if ($user?->role === 'cashier') {
            return $query->where('cashier_id', $user->id);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }
}
