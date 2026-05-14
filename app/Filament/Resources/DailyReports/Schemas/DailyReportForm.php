<?php

namespace App\Filament\Resources\DailyReports\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DailyReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan Laporan')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('report_date')
                            ->label('Tanggal Laporan')
                            ->disabled(),
                        TextInput::make('total_transactions')
                            ->label('Total Transaksi')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('total_sales')
                            ->label('Total Penjualan')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        TextInput::make('total_discount')
                            ->label('Total Diskon')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        TextInput::make('total_tax')
                            ->label('Total Pajak')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                    ]),

                Section::make('Rincian Pembayaran')
                    ->columns(3)
                    ->schema([
                        TextInput::make('total_cash')
                            ->label('Total Tunai')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        TextInput::make('total_debit')
                            ->label('Total Debit')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        TextInput::make('total_qris')
                            ->label('Total QRIS')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                    ]),

                Section::make('Saldo')
                    ->columns(2)
                    ->schema([
                        TextInput::make('opening_balance')
                            ->label('Saldo Awal')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        TextInput::make('closing_balance')
                            ->label('Saldo Akhir')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                    ]),
            ]);
    }
}
