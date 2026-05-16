<?php

namespace App\Filament\Widgets\Owner;

use App\Enums\UserRole;
use App\Models\Transaction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OwnerTokoTransactionsTable extends TableWidget
{
    protected static ?string $heading = 'Transaksi Toko Hari Ini';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->role === UserRole::Owner->value || Auth::user()?->role === 'owner';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Transaction::query()
                    ->where('toko_id', Auth::user()?->toko_id)
                    ->whereDate('created_at', today())
                    ->with('cashier')
                    ->latest('id')
                    ->limit(15)
            )
            ->columns([
                TextColumn::make('transaction_number')->label('No. Transaksi')->sortable(),
                TextColumn::make('cashier.name')->label('Kasir')->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Waktu')->time()->sortable(),
            ])
            ->striped()
            ->defaultPaginationPageOption(5);
    }
}
