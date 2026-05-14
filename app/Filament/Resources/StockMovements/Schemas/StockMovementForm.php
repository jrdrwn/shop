<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $product = Product::find($state);
                            if ($product) {
                                $set('quantity_before', $product->stock);
                            }
                        }
                    }),
                Select::make('movement_type')
                    ->label('Jenis Gerakan')
                    ->options([
                        'purchase' => 'Pembelian (Masuk)',
                        'sale' => 'Penjualan (Keluar)',
                        'adjustment' => 'Penyesuaian',
                        'return' => 'Retur (Masuk)',
                        'damage' => 'Rusak (Keluar)',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $before = $get('quantity_before') ?? 0;
                        $change = (int) $get('quantity_change') ?? 0;

                        $set('quantity_after', static::calculateAfter($state, $before, $change));
                    }),
                TextInput::make('quantity_change')
                    ->label('Perubahan Jumlah')
                    ->numeric()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $before = $get('quantity_before') ?? 0;
                        $type = $get('movement_type');

                        $set('quantity_after', static::calculateAfter($type, $before, (int) $state));
                    }),
                TextInput::make('quantity_before')
                    ->label('Jumlah Sebelum')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('quantity_after')
                    ->label('Jumlah Sesudah')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('reference_id')
                    ->label('ID Referensi')
                    ->placeholder('Opsional (No. Transaksi / PO)'),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function calculateAfter(?string $type, int $before, int $change): int
    {
        if ($type === 'purchase' || $type === 'return') {
            return $before + $change;
        } elseif ($type === 'sale' || $type === 'damage') {
            return $before - $change;
        } elseif ($type === 'adjustment') {
            return $before + $change; // Assuming change is delta
        }

        return $before;
    }
}
