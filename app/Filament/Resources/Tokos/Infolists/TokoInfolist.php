<?php

namespace App\Filament\Resources\Tokos\Infolists;

use Filament\Infolists\Components\TextEntry;
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

                Section::make('Pengaturan Transaksi')
                    ->description('Pajak dan biaya layanan yang diterapkan pada setiap transaksi.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('tax_percentage')
                            ->label('Pajak')
                            ->formatStateUsing(fn (int $state): string => $state > 0 ? "{$state}%" : 'Tidak ada pajak')
                            ->badge()
                            ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray'),
                        TextEntry::make('service_charge_percentage')
                            ->label('Service Charge')
                            ->formatStateUsing(fn (int $state): string => $state > 0 ? "{$state}%" : 'Tidak ada service charge')
                            ->badge()
                            ->color(fn (int $state): string => $state > 0 ? 'info' : 'gray'),
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

                Section::make('Langganan')
                    ->description('Paket aktif toko dan masa berlakunya.')
                    ->columns(2)
                    // ->visible($isSuperAdmin)
                    ->schema([
                        TextEntry::make('subscription.name')
                            ->label('Paket')
                            ->badge()
                            ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                                'free' => 'gray',
                                'plus' => 'warning',
                                'pro' => 'success',
                                default => 'gray',
                            })
                            ->placeholder('Belum diatur'),
                        TextEntry::make('subscription.duration_months')
                            ->label('Durasi (bulan)')
                            ->placeholder('Belum diatur'),
                        TextEntry::make('subscription.price')
                            ->label('Harga')
                            ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.'))
                            ->placeholder('Belum diatur'),
                    ]),
            ]);
    }
}
