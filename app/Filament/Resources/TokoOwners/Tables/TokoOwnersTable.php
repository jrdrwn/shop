<?php

namespace App\Filament\Resources\TokoOwners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TokoOwnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('toko.name')->label('Toko')->searchable()->sortable(),
                TextColumn::make('owner.name')->label('Owner')->searchable()->sortable(),
                TextColumn::make('assignedBy.name')->label('Assigned By')->toggleable(),
                BadgeColumn::make('toko.subscription.name')
                    ->label('Langganan')
                    ->colors([
                        'secondary' => fn ($state): bool => strtolower((string) $state) === 'free',
                        'warning' => fn ($state): bool => strtolower((string) $state) === 'plus',
                        'success' => fn ($state): bool => strtolower((string) $state) === 'pro',
                    ])
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('assigned_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
