<?php

namespace App\Filament\Resources\CashFlows\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashFlowsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                BadgeColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn (string $state): string => $state === 'income' ? 'Uang Masuk' : 'Uang Keluar')
                    ->colors([
                        'success' => 'income',
                        'danger' => 'expense',
                    ]),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sales' => 'Penjualan',
                        'payroll' => 'Gaji Karyawan',
                        'utilities' => 'Utilitas',
                        'supplies' => 'Suplai',
                        'equipment' => 'Peralatan',
                        'other' => 'Lainnya',
                        default => $state,
                    }),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(30),
                TextColumn::make('reference_id')
                    ->label('Ref ID')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'income' => 'Uang Masuk',
                        'expense' => 'Uang Keluar',
                    ]),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'sales' => 'Penjualan',
                        'payroll' => 'Gaji Karyawan',
                        'utilities' => 'Utilitas',
                        'supplies' => 'Suplai',
                        'equipment' => 'Peralatan',
                        'other' => 'Lainnya',
                    ]),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Hingga Tanggal'),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
