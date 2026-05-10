<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Gambar')
                    ->circular()
                    ->disk('public')
                    ->imageSize(32),
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('price')
                    ->label('Harga')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn ($state): string => (string) $state)
                    ->color(fn ($state): string => (int) $state <= 5 ? 'warning' : 'success'),
                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn ($state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->colors([
                        'success' => true,
                        'gray' => false,
                    ]),
                BadgeColumn::make('has_variants')
                    ->label('Varian')
                    ->formatStateUsing(fn ($state): string => $state ? 'Ada Varian' : '-')
                    ->colors([
                        'primary' => true,
                        'gray' => false,
                    ]),
            ])
            ->groups([
                \Filament\Tables\Grouping\Group::make('category.name')
                    ->label('Kategori'),
            ])
            ->filters([
                // future filters (category, price range)
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
