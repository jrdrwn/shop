<?php

namespace App\Filament\Resources\Tokos\Schemas;

use App\Models\Subscription;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TokoForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = Auth::user()?->role === 'super_admin';

        return $schema
            ->components([

                Section::make('Identitas Toko')
                    ->description('Data dasar toko yang digunakan untuk dashboard, transaksi, dan laporan.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Toko')
                            ->required()
                            ->placeholder('Contoh: Toko Maju')
                            ->maxLength(255),
                        TextInput::make('owner_name')
                            ->label('Nama Pemilik')
                            ->placeholder('Nama pemilik toko')
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
                            ->helperText('Toko nonaktif akan disembunyikan dari pemilihan data utama.'),
                    ]),

                Section::make('Pengaturan Transaksi')
                    ->description('Tax dan service charge yang diterapkan pada setiap transaksi di toko ini.')
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
                    ->description('Pemilihan paket langganan ada di halaman utama / Dashboard Owner. ')
                    ->disabled(true)
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
                Section::make('Payment Gateway (QRIS)')
                    ->description('Konfigurasi bagaimana QRIS diproses.')
                    ->schema([
                        Select::make('qris_type')
                            ->label('Tipe QRIS')
                            ->options([
                                'manual' => 'Manual (Scan Statis / Foto)',
                                'midtrans' => 'Otomatis (Midtrans Dynamic QRIS)',
                            ])
                            ->required()
                            ->live(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('midtrans_merchant_id')
                                    ->label('Midtrans Merchant ID')
                                    ->placeholder('Gxxxxxxxxx')
                                    ->required(fn ($get) => $get('qris_type') === 'midtrans'),
                                TextInput::make('midtrans_client_key')
                                    ->label('Midtrans Client Key')
                                    ->placeholder('SB-Mid-client-xxxxxxxx')
                                    ->required(fn ($get) => $get('qris_type') === 'midtrans'),
                                TextInput::make('midtrans_server_key')
                                    ->label('Midtrans Server Key')
                                    ->password()
                                    ->revealable()
                                    ->placeholder('SB-Mid-server-xxxxxxxx')
                                    ->required(fn ($get) => $get('qris_type') === 'midtrans'),
                                Toggle::make('midtrans_is_production')
                                    ->label('Mode Produksi (Otomatis)')
                                    ->helperText('Status mengikuti pengaturan sistem: '.(config('midtrans.is_production') ? 'PRODUCTION' : 'SANDBOX'))
                                    ->default(config('midtrans.is_production'))
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->visible(fn ($get) => $get('qris_type') === 'midtrans'),
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
                            ->disk('public')
                            ->image()
                            ->directory('toko-logos')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageAspectRatio('1:1')
                            ->automaticallyOpenImageEditorForAspectRatio()
                            ->maxSize(2048) // 1MB
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
            ]);
    }
}
