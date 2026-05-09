<?php

namespace App\Filament\Widgets;

use App\Models\Cafe;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SuperAdminCafeSummaryTable extends TableWidget
{
    protected static ?string $heading = 'Cafe, Manager & Subscription';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return Auth::user()?->role === 'super_admin';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Cafe::query()->with(['manager.manager', 'subscription'])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Cafe')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('city')
                    ->label('Kota')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('manager.manager.name')
                    ->label('Manager')
                    ->placeholder('Belum ditetapkan')
                    ->sortable(),
                TextColumn::make('subscription.name')
                    ->label('Subscription')
                    ->badge()
                    ->placeholder('Tidak ada')
                    ->color('success'),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->striped()
            ->paginated(false);
    }
}
