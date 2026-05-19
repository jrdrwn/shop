<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use App\Enums\SubscriptionPlan;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        $role = Auth::user()?->role;
        $isOwner = is_string($role) && in_array($role, ['owner'], true);

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Paket')
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('plan')
                    ->label('Tipe')
                    ->formatStateUsing(fn (SubscriptionPlan $state): string => $state->getLabel())
                    ->colors([
                        'gray' => SubscriptionPlan::Free->value,
                        'primary' => [SubscriptionPlan::Medium->value, SubscriptionPlan::Premium->value],
                    ])
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Harga')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
                TextColumn::make('duration_months')
                    ->label('Durasi')
                    ->formatStateUsing(fn ($state): string => $state.' bln')
                    ->sortable(),
                TextColumn::make('limits_summary')
                    ->label('Batas Utama')
                    ->state(function ($record): string {
                        $products = $record->getLimit('max_products');
                        $categories = $record->getLimit('max_categories');

                        $productLabel = $products === null ? 'Produk ∞' : "Produk {$products}";
                        $categoryLabel = $categories === null ? 'Kat. ∞' : "Kat. {$categories}";

                        return "{$productLabel} | {$categoryLabel}";
                    })
                    ->color('gray'),
                IconColumn::make('limits.can_use_inventory')
                    ->label('Inventori')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->state(fn ($record): bool => $record->hasFeature('can_use_inventory')),
                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn ($state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->colors(['success' => 1, 'secondary' => 0]),
            ])
            ->filters([
                SelectFilter::make('plan')
                    ->label('Tipe Paket')
                    ->options(SubscriptionPlan::class),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Nonaktif',
                    ]),
            ])
            ->recordActions(
                $isOwner
                    ? [ViewAction::make()]
                    : [ViewAction::make(), EditAction::make()]
            )
            ->toolbarActions([]);
    }
}
