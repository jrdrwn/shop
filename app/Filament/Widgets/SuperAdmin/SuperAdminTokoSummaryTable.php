<?php

namespace App\Filament\Widgets\SuperAdmin;

use App\Models\Toko;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SuperAdminTokoSummaryTable extends TableWidget
{
    protected static ?string $heading = 'Toko, Owner & Subscription';

    protected int|string|array $columnSpan = 2;

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Auth::user()?->role === 'super_admin';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Toko::query()
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Toko')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('city')
                    ->label('Kota')
                    ->sortable()
                    ->placeholder('-'),
                // TextColumn::make('Owner.name')
                //     ->label('owner')
                //     ->placeholder('Belum ditetapkan')
                //     ->sortable(),
                // TextColumn::make('subscription.name')
                //     ->label('Subscription')
                //     ->badge()
                //     ->placeholder('Tidak ada')
                //     ->color('success'),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->striped()
            ->paginated(true);
    }
}
