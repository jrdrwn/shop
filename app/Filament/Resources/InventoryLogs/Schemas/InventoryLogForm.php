<?php

namespace App\Filament\Resources\InventoryLogs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class InventoryLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Select::make('toko_id')->relationship('toko', 'name')->required()->label('Toko'),
                    Select::make('product_id')->relationship('product', 'name')->required()->label('Product'),
                    Select::make('action')->options([
                        'sale' => 'Sale',
                        'adjustment' => 'Adjustment',
                        'return' => 'Return',
                    ])->required(),
                    TextInput::make('quantity_change')->numeric()->required()->label('Quantity Change'),
                    TextInput::make('quantity_before')->numeric()->required()->label('Quantity Before'),
                    TextInput::make('quantity_after')->numeric()->required()->label('Quantity After'),
                    TextInput::make('reference_id')->numeric()->label('Reference ID'),
                    TextInput::make('reference_type')->label('Reference Type'),
                    Select::make('created_by')->relationship('creator', 'name')->required()->label('Created By'),
                    Textarea::make('notes')->label('Notes')->columnSpanFull(),
                ]),
            ]);
    }
}
