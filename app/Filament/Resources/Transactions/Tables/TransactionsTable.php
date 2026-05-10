<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_number')
                    ->label('Nomor Transaksi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cashier.name')
                    ->label('Kasir')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->sortable()
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format((int) $state, 0, ',', '.')),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function ($state): string {
                        return match ($state) {
                            'pending' => 'Pending',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                            default => (string) $state,
                        };
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('hari_ini')
                    ->label('Hari Ini')
                    ->query(fn(Builder $query): Builder => $query->whereDate('created_at', today())),
                Filter::make('minggu_ini')
                    ->label('Minggu Ini')
                    ->query(fn(Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),
                Filter::make('bulan_ini')
                    ->label('Bulan Ini')
                    ->query(fn(Builder $query): Builder => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)),
            ]);
    }
}
