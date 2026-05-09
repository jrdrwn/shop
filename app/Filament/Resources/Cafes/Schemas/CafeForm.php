<?php

namespace App\Filament\Resources\Cafes\Schemas;

use App\Models\Subscription;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CafeForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = Auth::user()?->role === 'super_admin';

        return $schema
            ->components([

                Section::make('Identitas Cafe')
                    ->description('Data dasar cafe yang digunakan untuk dashboard, transaksi, dan laporan.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Cafe')
                            ->required()
                            ->placeholder('Contoh: Caffe Maju')
                            ->maxLength(255),
                        TextInput::make('owner_name')
                            ->label('Nama Pemilik')
                            ->placeholder('Nama pemilik cafe')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->placeholder('owner@domain.com')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->placeholder('08xxxxxxxxxx')
                            ->tel()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->visible($isSuperAdmin)
                            ->helperText('Cafe nonaktif akan disembunyikan dari pemilihan data utama.'),
                    ]),

                Section::make('Lokasi & Brand')
                    ->description('Tambahkan alamat dan aset visual agar tampilan lebih profesional.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('city')
                            ->label('Kota')
                            ->placeholder('Bandung')
                            ->maxLength(255),
                        TextInput::make('province')
                            ->label('Provinsi')
                            ->placeholder('Jawa Barat')
                            ->maxLength(255),
                        FileUpload::make('logo_url')
                            ->label('Logo')
                            ->image()
                            ->directory('cafes')
                            ->columnSpanFull(),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Pengaturan Transaksi')
                    ->description('Tax dan service charge yang diterapkan pada setiap transaksi di cafe ini.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('tax_percentage')
                            ->label('Pajak (%)')
                            ->helperText('Contoh: 11 = PPN 11%. Isi 0 jika tidak ada pajak.')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(0)
                            ->required(),
                        TextInput::make('service_charge_percentage')
                            ->label('Service Charge (%)')
                            ->helperText('Contoh: 5 = biaya layanan 5%. Isi 0 jika tidak ada.')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(0)
                            ->required(),
                    ]),

                // Subscription assignment — super admin only
                Section::make('Langganan')
                    ->description('Pilih paket langganan yang sesuai dengan kebutuhan cafe.')
                    ->visible($isSuperAdmin)
                    ->schema([
                        Select::make('subscription_id')
                            ->label('Paket Langganan')
                            ->placeholder('Pilih paket langganan...')
                            ->options(
                                Subscription::whereIsActive(true)
                                    ->orderBy('price', 'asc')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->nullable(),
                    ]),

            ]);
    }
}
