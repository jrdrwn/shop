<?php

namespace App\Filament\Resources\Cafes\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CafeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = Auth::user()?->role === 'super_admin';

        return $schema
            ->components([
                Section::make('Informasi Cafe')
                    ->description('Ringkasan identitas cafe yang aktif di sistem.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Cafe'),
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

                Section::make('Manager')
                    ->description('Informasi manager yang ditugaskan pada cafe ini.')
                    ->columns(2)
                    ->visible($isSuperAdmin)
                    ->schema([
                        TextEntry::make('manager.manager.name')
                            ->label('Manager')
                            ->placeholder('Belum ditetapkan'),
                        TextEntry::make('manager.assigned_at')
                            ->label('Assigned At')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Belum ditetapkan'),
                    ]),

                Section::make('Langganan')
                    ->description('Paket aktif cafe dan masa berlakunya.')
                    ->columns(2)
                    ->visible($isSuperAdmin)
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
