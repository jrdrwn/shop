<?php

namespace App\Filament\Resources\Cafes\Tables;

use App\Filament\Resources\Cafes\CafeResource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CafesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Cafe')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('manager.manager.name')
                    ->label('Manager')
                    ->placeholder('Belum ditetapkan')
                    ->searchable(),
                BadgeColumn::make('subscription.name')
                    ->label('Langganan')
                    ->colors([
                        'secondary' => fn ($state): bool => strtolower((string) $state) === 'free',
                        'warning' => fn ($state): bool => strtolower((string) $state) === 'plus',
                        'success' => fn ($state): bool => strtolower((string) $state) === 'pro',
                    ])
                    ->sortable()
                    ->searchable(),
                TextColumn::make('owner_name')
                    ->label('Pemilik')
                    ->toggleable(),
                TextColumn::make('city')
                    ->label('Kota')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                // Edit only visible when the current user can edit this record
                EditAction::make()
                    ->visible(fn ($record): bool => CafeResource::canEdit($record)),
            ])
            ->toolbarActions([
                // No bulk delete — cafes are read-only from the panel
            ]);
    }
}
