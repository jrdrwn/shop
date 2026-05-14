<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi')
                    ->description('Ringkasan transaksi beserta kasir dan status prosesnya.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('transaction_number')
                            ->label('Nomor Transaksi')
                            ->required()
                            ->placeholder('TRX-20260508-0001')
                            ->maxLength(255),
                        Select::make('cashier_id')
                            ->label('Kasir')
                            ->relationship('cashier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('toko_id')
                            ->label('Toko')
                            ->relationship('toko', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                    ]),
                Section::make('Nilai & Catatan')
                    ->description('Nominal transaksi harus sinkron dengan checkout dan payment log.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Total')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0),
                        TextInput::make('discount_amount')
                            ->label('Diskon')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0),
                        TextInput::make('tax_amount')
                            ->label('Pajak')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0),
                        TextInput::make('paid_amount')
                            ->label('Dibayar')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0),
                        TextInput::make('change_amount')
                            ->label('Kembalian')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
