<?php

namespace App\Filament\Resources\Subscriptions\Infolists;

use App\Enums\SubscriptionPlan;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Rincian Paket')
                    ->description('Informasi dasar paket langganan ini.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Paket'),
                        TextEntry::make('plan')
                            ->label('Tipe Paket')
                            ->badge()
                            ->formatStateUsing(fn (SubscriptionPlan $state): string => $state->getLabel())
                            ->color(fn (SubscriptionPlan $state): string => $state->getColor()),
                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                        TextEntry::make('price')
                            ->label('Harga')
                            ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
                        TextEntry::make('duration_months')
                            ->label('Durasi')
                            ->formatStateUsing(fn ($state): string => $state.' bulan'),
                    ]),

                Section::make('Batas Kapasitas')
                    ->description('Batas sumber daya yang berlaku untuk paket ini (dari tipe paket + kustomisasi).')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('effective_max_products')
                            ->label('Maks. Produk')
                            ->state(fn ($record): string => $record->getLimit('max_products') === null
                                ? 'Tidak terbatas'
                                : (string) $record->getLimit('max_products')
                            ),
                        TextEntry::make('effective_max_categories')
                            ->label('Maks. Kategori')
                            ->state(fn ($record): string => $record->getLimit('max_categories') === null
                                ? 'Tidak terbatas'
                                : (string) $record->getLimit('max_categories')
                            ),
                        TextEntry::make('effective_max_staff')
                            ->label('Maks. Staff')
                            ->state(fn ($record): string => $record->getLimit('max_staff') === null
                                ? 'Tidak terbatas'
                                : (string) $record->getLimit('max_staff')
                            ),
                        TextEntry::make('effective_max_payment_methods')
                            ->label('Maks. Metode Pembayaran')
                            ->state(fn ($record): string => $record->getLimit('max_payment_methods') === null
                                ? 'Tidak terbatas'
                                : (string) $record->getLimit('max_payment_methods')
                            ),
                    ]),

                Section::make('Akses Fitur')
                    ->description('Fitur lanjutan yang tersedia untuk paket ini.')
                    ->columns(2)
                    ->schema([
                        IconEntry::make('can_export_reports')
                            ->label('Ekspor Laporan')
                            ->state(fn ($record): bool => $record->hasFeature('can_export_reports'))
                            ->boolean(),
                        IconEntry::make('can_use_inventory')
                            ->label('Manajemen Inventori')
                            ->state(fn ($record): bool => $record->hasFeature('can_use_inventory'))
                            ->boolean(),
                        IconEntry::make('can_use_variants')
                            ->label('Varian Produk')
                            ->state(fn ($record): bool => $record->hasFeature('can_use_variants'))
                            ->boolean(),
                        IconEntry::make('can_use_discounts')
                            ->label('Diskon Produk')
                            ->state(fn ($record): bool => $record->hasFeature('can_use_discounts'))
                            ->boolean(),
                    ]),

                Section::make('Deskripsi Fitur (Marketing)')
                    ->description('Teks promosi yang ditampilkan ke pengguna.')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('features')
                            ->label('')
                            ->formatStateUsing(function ($state): string {
                                if (is_array($state)) {
                                    return implode('<br>', array_map(fn ($f) => '&#x2714; '.$f, array_filter($state)));
                                }

                                return (string) $state;
                            })
                            ->html(),
                    ]),

            ]);
    }
}
