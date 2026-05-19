<?php

namespace App\Filament\Resources\Tokos\Tables;

use App\Filament\Resources\Tokos\TokoResource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TokosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Toko')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('owner.name')
                    ->label('Owner')
                    ->placeholder('Belum ditetapkan')
                    ->searchable()
                    ->hidden(fn () => auth()->user()?->role === 'super_admin'),
                BadgeColumn::make('subscription.name')
                    ->label('Langganan')
                    ->colors([
                        'gray' => fn ($state): bool => strtolower((string) $state) === 'free',
                        'primary' => fn ($state): bool => in_array(strtolower((string) $state), ['medium', 'premium'], true),
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
                    ->visible(fn ($record): bool => TokoResource::canEdit($record)),
            ])
            ->toolbarActions([
                // No bulk delete — tokos are read-only from the panel
            ]);
    }
}
