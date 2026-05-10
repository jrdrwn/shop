<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Enums\SubscriptionPlan;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Rincian Paket')
                    ->description('Informasi dasar paket langganan yang ditampilkan ke pengguna.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Paket')
                            ->required()
                            ->placeholder('Contoh: Pro Bulanan')
                            ->maxLength(255),
                        Select::make('plan')
                            ->label('Tipe Paket')
                            ->options(SubscriptionPlan::class)
                            ->required()
                            ->default(SubscriptionPlan::Free->value)
                            ->live()
                            ->afterStateUpdated(function (SubscriptionPlan|string|null $state, callable $set): void {
                                if (! $state) {
                                    return;
                                }
                                $plan = $state instanceof SubscriptionPlan ? $state : SubscriptionPlan::from($state);
                                $defaults = $plan->defaultLimits();
                                $set('limits.max_products', $defaults['max_products']);
                                $set('limits.max_categories', $defaults['max_categories']);
                                $set('limits.max_staff', $defaults['max_staff']);
                                $set('limits.max_payment_methods', $defaults['max_payment_methods']);
                                $set('limits.can_export_reports', $defaults['can_export_reports']);
                                $set('limits.can_use_inventory', $defaults['can_use_inventory']);
                                $set('limits.can_use_variants', $defaults['can_use_variants']);
                                $set('limits.can_use_discounts', $defaults['can_use_discounts']);
                                $set('price', $plan->price());
                                $set('duration_months', $plan->durationMonths());
                            })
                            ->helperText(fn (SubscriptionPlan|string|null $state): string => $state
                                ? ($state instanceof SubscriptionPlan ? $state->description() : SubscriptionPlan::from($state)->description())
                                : 'Pilih tipe paket untuk mengisi batas fitur secara otomatis.'
                            ),
                        TextInput::make('price')
                            ->label('Harga')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0),
                        TextInput::make('duration_months')
                            ->label('Durasi (bulan)')
                            ->required()
                            ->numeric()
                            ->suffix('bulan')
                            ->minValue(1),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Paket nonaktif tidak bisa dipilih saat membuat langganan baru.'),
                        Textarea::make('features')
                            ->label('Deskripsi Fitur (Marketing)')
                            ->helperText('Teks promosi yang ditampilkan ke pengguna. Satu baris per fitur.')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Batas Kapasitas')
                    ->description('Jumlah maksimum untuk setiap sumber daya. Kosongkan (hapus angka) untuk tidak terbatas.')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('limits_hint')
                            ->label('')
                            ->content('Pilih tipe paket di atas untuk mengisi otomatis. Anda dapat mengubah nilai berikut secara manual.')
                            ->columnSpanFull(),
                        TextInput::make('limits.max_products')
                            ->label('Maks. Produk')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Tidak terbatas')
                            ->helperText('Jumlah produk maksimum yang bisa dibuat oleh cafe.'),
                        TextInput::make('limits.max_categories')
                            ->label('Maks. Kategori')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Tidak terbatas')
                            ->helperText('Jumlah kategori maksimum.'),
                        TextInput::make('limits.max_staff')
                            ->label('Maks. Staff')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Tidak terbatas')
                            ->helperText('Jumlah manager/kasir yang bisa terdaftar.'),
                        TextInput::make('limits.max_payment_methods')
                            ->label('Maks. Metode Pembayaran')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Tidak terbatas')
                            ->helperText('Jumlah metode pembayaran yang bisa ditambahkan.'),
                    ]),

                Section::make('Akses Fitur')
                    ->description('Pilih fitur lanjutan yang tersedia untuk paket ini.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('limits.can_export_reports')
                            ->label('Ekspor Laporan')
                            ->helperText('Izinkan ekspor laporan harian/bulanan ke PDF atau Excel.'),
                        Toggle::make('limits.can_use_inventory')
                            ->label('Manajemen Inventori')
                            ->helperText('Izinkan pencatatan dan pengelolaan stok bahan baku.'),
                        Toggle::make('limits.can_use_variants')
                            ->label('Varian Produk')
                            ->helperText('Izinkan produk memiliki varian (ukuran, suhu, dll.).'),
                        Toggle::make('limits.can_use_discounts')
                            ->label('Diskon Produk')
                            ->helperText('Izinkan pengaturan diskon per produk.'),
                    ]),

            ]);
    }
}
