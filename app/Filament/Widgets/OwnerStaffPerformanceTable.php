<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OwnerStaffPerformanceTable extends TableWidget
{
    protected static ?string $heading = 'Kinerja Kasir';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return Auth::user()?->role === UserRole::Owner->value || Auth::user()?->role === 'owner';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => User::query()
                    ->where('toko_id', Auth::user()?->toko_id)
                    ->where('role', 'kasir')
                    ->where('is_active', true)
                    ->withCount([
                        'transactions as today_count' => fn (Builder $q) => $q
                            ->whereDate('created_at', today()),
                    ])
                    ->withSum([
                        'transactions as month_revenue' => fn (Builder $q) => $q
                            ->where('status', 'completed')
                            ->where('created_at', '>=', now()->startOfMonth()),
                    ], 'total_amount')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Kasir')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('today_count')
                    ->label('Tx Hari Ini')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('month_revenue')
                    ->label('Omzet Bulan Ini')
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.'))
                    ->sortable(),
            ])
            ->emptyStateHeading('Belum ada kasir aktif')
            ->striped()
            ->searchable(false)
            ->defaultPaginationPageOption(5);
    }
}
