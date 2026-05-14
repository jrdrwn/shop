<?php

namespace App\Filament\Resources\TokoOwners\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TokoOwnerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Penugasan')
                    ->description('Detail hubungan owner dengan toko yang ditugaskan.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('toko.name')
                            ->label('Toko'),
                        TextEntry::make('owner.name')
                            ->label('Owner'),
                        TextEntry::make('assigned_at')
                            ->label('Assigned At')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Belum ditetapkan'),
                        TextEntry::make('assignedBy.name')
                            ->label('Assigned By')
                            ->placeholder('Belum ditetapkan'),
                    ]),
                Section::make('Toko')
                    ->description('Info toko yang dipakai oleh owner ini.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('toko.owner_name')
                            ->label('Nama Pemilik')
                            ->placeholder('Belum diisi'),
                        TextEntry::make('toko.is_active')
                            ->label('Status Toko')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                    ]),
                Section::make('Langganan')
                    ->description('Paket aktif toko beserta masa berlakunya.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('toko.subscription.name')
                            ->label('Paket')
                            ->badge()
                            ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                                'free' => 'gray',
                                'plus' => 'warning',
                                'pro' => 'success',
                                default => 'gray',
                            })
                            ->placeholder('Belum diatur'),
                        TextEntry::make('toko.subscription.duration_months')
                            ->label('Durasi (bulan)')
                            ->placeholder('Belum diatur'),
                        TextEntry::make('toko.subscription.price')
                            ->label('Harga')
                            ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.'))
                            ->placeholder('Belum diatur'),
                    ]),
            ]);
    }
}
