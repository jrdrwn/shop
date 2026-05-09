<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CashierTodayTransactionsTable extends TableWidget
{
    protected static ?string $heading = 'Transaksi Saya Hari Ini';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return Auth::user()?->role === 'cashier';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Transaction::query()
                    ->where('cashier_id', Auth::id())
                    ->whereDate('created_at', today())
                    ->latest('id')
                    ->limit(20)
            )
            ->columns([
                TextColumn::make('transaction_number')->label('No. Transaksi')->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Waktu')->dateTime('H:i'),
            ])
            ->striped()
            ->paginated(false);
    }
}
