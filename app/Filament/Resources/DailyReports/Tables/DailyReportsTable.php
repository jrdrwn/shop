<?php

namespace App\Filament\Resources\DailyReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DailyReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('total_transactions')
                    ->label('Transaksi')
                    ->sortable(),
                TextColumn::make('total_sales')
                    ->label('Total Penjualan')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.'))
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('total_discount')
                    ->label('Diskon')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
                TextColumn::make('total_tax')
                    ->label('Pajak')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
                TextColumn::make('total_cash')
                    ->label('Tunai')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
                TextColumn::make('total_debit')
                    ->label('Debit')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
                TextColumn::make('total_qris')
                    ->label('QRIS')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
            ])
            ->filters([
                Filter::make('report_date')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Hingga Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('report_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('report_date', '<=', $date),
                            );
                    }),
                Filter::make('minimum_amount')
                    ->form([
                        TextInput::make('min_sales')->label('Penjualan Minimal')->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_sales'],
                                fn (Builder $query, $amount): Builder => $query->where('total_sales', '>=', $amount),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
