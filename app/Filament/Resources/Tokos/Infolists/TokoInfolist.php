<?php

namespace App\Filament\Resources\Tokos\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TokoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = Auth::user()?->role === 'super_admin';

        return $schema
            ->components([
                Grid::make(1)->schema([
                    Section::make('Informasi Toko')
                        ->description('Ringkasan identitas toko yang aktif di sistem.')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('name')
                                ->label('Nama Toko'),
                            TextEntry::make('owner_name')
                                ->label('Pemilik')
                                ->placeholder('Belum diisi'),
                            TextEntry::make('phone')
                                ->label('Telepon')
                                ->placeholder('Belum diisi'),
                            TextEntry::make('email')
                                ->label('Email')
                                ->placeholder('Belum diisi'),
                            TextEntry::make('city')
                                ->label('Kota')
                                ->placeholder('Belum diisi'),
                            TextEntry::make('province')
                                ->label('Provinsi')
                                ->placeholder('Belum diisi'),
                        ]),


                    Section::make('Langganan')
                        ->description('Paket aktif toko dan masa berlakunya.')
                        ->columns(2)
                        // ->visible($isSuperAdmin)
                        ->schema([
                            TextEntry::make('subscription.name')
                                ->label('Paket')
                                ->badge()
                                ->color(fn(?string $state): string => match (strtolower((string) $state)) {
                                    'free' => 'gray',
                                    'medium', 'premium' => 'primary',
                                    default => 'gray',
                                })
                                ->placeholder('Belum diatur'),
                            TextEntry::make('subscription.duration_months')
                                ->label('Durasi (bulan)')
                                ->placeholder('Belum diatur'),
                            TextEntry::make('subscription.price')
                                ->label('Harga')
                                ->formatStateUsing(fn($state): string => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                                ->placeholder('Belum diatur'),
                        ]),
                ]),
                Grid::make(1)->schema([

                    Section::make('Pengaturan Transaksi')
                        ->description('Pajak dan biaya layanan yang diterapkan pada setiap transaksi.')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('tax_percentage')
                                ->label('Pajak')
                                ->formatStateUsing(fn(int $state): string => $state > 0 ? "{$state}%" : 'Tidak ada pajak')
                                ->badge()
                                ->color(fn(int $state): string => $state > 0 ? 'warning' : 'gray'),
                            TextEntry::make('service_charge_percentage')
                                ->label('Service Charge')
                                ->formatStateUsing(fn(int $state): string => $state > 0 ? "{$state}%" : 'Tidak ada service charge')
                                ->badge()
                                ->color(fn(int $state): string => $state > 0 ? 'info' : 'gray'),
                        ]),
                    Section::make('Owner')
                        ->description('Informasi Owner yang ditugaskan pada toko ini.')
                        ->columns(2)
                        ->visible($isSuperAdmin)
                        ->schema([
                            TextEntry::make('owner.name')
                                ->label('Owner')
                                ->placeholder('Belum ditetapkan'),
                        ]),

                    Section::make('Payment Gateway')
                        ->description('Konfigurasi Midtrans untuk pembayaran otomatis.')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('qris_type')
                                ->label('Tipe QRIS')
                                ->formatStateUsing(fn(string $state): string => strtoupper($state))
                                ->badge(),
                            TextEntry::make('midtrans_merchant_id')
                                ->label('Merchant ID')
                                ->placeholder('-')
                                ->visible(fn($record) => $record->qris_type === 'midtrans'),
                            TextEntry::make('midtrans_client_key')
                                ->label('Client Key')
                                ->placeholder('-')
                                ->copyable()
                                ->visible(fn($record) => $record->qris_type === 'midtrans'),
                            TextEntry::make('midtrans_server_key')
                                ->label('Server Key')
                                ->placeholder('-')
                                ->formatStateUsing(fn($state) => $state ? '********' : '-')
                                ->visible(fn($record) => $record->qris_type === 'midtrans'),
                            TextEntry::make('midtrans_is_production')
                                ->label('Environment')
                                ->formatStateUsing(fn($state) => $state ? 'PRODUCTION' : 'SANDBOX')
                                ->badge()
                                ->color(fn($state) => $state ? 'success' : 'warning')
                                ->visible(fn($record) => $record->qris_type === 'midtrans'),
                        ]),
                ])
            ]);
    }
}
