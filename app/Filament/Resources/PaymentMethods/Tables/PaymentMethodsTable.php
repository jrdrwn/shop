<?php

namespace App\Filament\Resources\PaymentMethods\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 3,
            ])
            ->columns([
                Stack::make([
                    TextColumn::make('name')
                        ->label('Metode')
                        ->searchable()
                        ->sortable()
                        ->weight('bold')
                        ->size('lg'),
                    TextColumn::make('type')
                        ->label('Jenis')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'cash' => 'Tunai',
                            'debit' => 'Debit / Kartu',
                            default => 'QRIS',
                        }),
                    TextColumn::make('is_active')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                        ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                ]),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
