<?php

namespace App\Filament\Resources\StockMovements\Tables;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('movement_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'purchase' => 'success',
                        'sale' => 'danger',
                        'adjustment' => 'warning',
                        'return' => 'info',
                        'damage' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'purchase' => 'Pembelian',
                        'sale' => 'Penjualan',
                        'adjustment' => 'Penyesuaian',
                        'return' => 'Retur',
                        'damage' => 'Rusak',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('quantity_change')
                    ->label('Perubahan')
                    ->sortable(),
                TextColumn::make('quantity_before')
                    ->label('Sebelum')
                    ->sortable(),
                TextColumn::make('quantity_after')
                    ->label('Sesudah')
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('movement_type')
                    ->label('Jenis Gerakan')
                    ->options([
                        'purchase' => 'Pembelian',
                        'sale' => 'Penjualan',
                        'adjustment' => 'Penyesuaian',
                        'return' => 'Retur',
                        'damage' => 'Rusak',
                    ]),
                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name'),
                Filter::make('created_at')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
