<?php

namespace App\Filament\Resources\CashFlows\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CashFlowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kas')
                    ->description('Catat detail uang masuk atau keluar.')
                    ->columns(2)
                    ->schema([
                        Select::make('toko_id')
                            ->label('Toko')
                            ->relationship('toko', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn () => Auth::user()?->role === 'super_admin' || Auth::user()?->role === 'admin')
                            ->default(fn () => Auth::user()?->toko_id),

                        Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'income' => 'Uang Masuk (Income)',
                                'expense' => 'Uang Keluar (Expense)',
                            ])
                            ->required()
                            ->live(),

                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'sales' => 'Penjualan',
                                'payroll' => 'Gaji Karyawan',
                                'utilities' => 'Utilitas (Listrik, Air, dll)',
                                'supplies' => 'Suplai / Bahan Baku',
                                'equipment' => 'Peralatan',
                                'other' => 'Lainnya',
                            ])
                            ->required(),

                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->required(),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->maxLength(255),

                        TextInput::make('reference_id')
                            ->label('ID Referensi')
                            ->helperText('Optional: Link ke ID transaksi atau pembayaran'),

                        TextInput::make('reference_type')
                            ->label('Jenis Referensi')
                            ->helperText('Optional: transaction, payment, dll'),
                    ]),
            ]);
    }
}
