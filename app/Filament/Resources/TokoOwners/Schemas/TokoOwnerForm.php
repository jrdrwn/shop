<?php

namespace App\Filament\Resources\TokoOwners\Schemas;

use App\Models\TokoOwner;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class TokoOwnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Toko Owner')
                    ->tabs([
                        Tab::make('Toko')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('toko_id')
                                        ->relationship('toko', 'name')
                                        ->required()
                                        ->label('Toko'),
                                    Select::make('assigned_by')
                                        ->relationship('assignedBy', 'name')
                                        ->label('Assigned By'),
                                ]),
                            ]),
                        Tab::make('Owner')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('owner_id')
                                        ->relationship('owner', 'name')
                                        ->required()
                                        ->label('Owner'),
                                    DateTimePicker::make('assigned_at')
                                        ->label('Assigned At'),
                                ]),
                            ]),
                        Tab::make('Ringkasan')
                            ->schema([
                                Grid::make(1)->schema([
                                    Placeholder::make('subscription_name')
                                        ->label('Langganan Toko')
                                        ->content(fn (?TokoOwner $record): string => $record?->toko?->subscription?->name ?? 'Belum diatur'),
                                ]),
                            ]),
                    ]),
            ]);
    }
}
