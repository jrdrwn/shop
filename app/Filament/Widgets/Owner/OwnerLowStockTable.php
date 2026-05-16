<?php

namespace App\Filament\Widgets\Owner;

use App\Enums\UserRole;
use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OwnerLowStockTable extends TableWidget
{
    protected static ?string $heading = 'Stok Menipis (≤ 10)';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        return in_array(Auth::user()?->role, [UserRole::Owner->value, 'owner', UserRole::Warehouse->value, 'gudang']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Product::query()
                    ->where('toko_id', Auth::user()?->toko_id)
                    ->where('is_active', true)
                    ->where('stock', '<=', 10)
                    ->orderBy('stock', 'asc')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Produk')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->badge()
                    ->color(fn (int $state): string => $state <= 5 ? 'danger' : 'warning')
                    ->sortable(),
            ])
            ->emptyStateHeading('Semua stok aman')
            ->emptyStateDescription('Tidak ada produk yang stoknya menipis.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->striped()
            ->searchable(false)
            ->defaultPaginationPageOption(5);
    }
}
