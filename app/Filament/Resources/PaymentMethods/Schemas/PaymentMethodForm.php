<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konteks Pembayaran')
                    ->description('Hubungkan metode pembayaran ke toko yang tepat agar transaksi tetap tertata.')
                    ->columns(2)
                    ->schema([
                        Select::make('toko_id')
                            ->label('Toko')
                            ->relationship('toko', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn (): ?int => Auth::user()?->toko_id)
                            ->disabled(fn (): bool => Auth::user()?->role === 'owner')
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Metode')
                            ->required()
                            ->placeholder('Contoh: Cash Counter 1')
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'cash' => 'Tunai',
                                'debit' => 'Debit / Kartu',
                                'qris' => 'QRIS',
                            ])
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Metode nonaktif disembunyikan dari proses checkout.'),
                    ]),
            ]);
    }
}
