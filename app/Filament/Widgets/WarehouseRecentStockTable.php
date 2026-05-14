<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\StockMovement;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class WarehouseRecentStockTable extends BaseWidget
{
    protected static ?string $heading = 'Riwayat Stok Terbaru';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return in_array(Auth::user()?->role, [UserRole::Warehouse->value, 'gudang', UserRole::Owner->value, 'owner']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => StockMovement::query()
                    ->where('toko_id', Auth::user()?->toko_id)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M H:i')
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('movement_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'purchase', 'return' => 'success',
                        'sale', 'damage' => 'danger',
                        'adjustment' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'purchase' => 'Masuk',
                        'sale' => 'Keluar',
                        'adjustment' => 'Penyesuaian',
                        'return' => 'Retur',
                        'damage' => 'Rusak',
                        default => ucfirst($state),
                    }),
                TextColumn::make('quantity_change')
                    ->label('Perubahan')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray'))
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? '+' . $state : (string) $state),
            ])
            ->emptyStateHeading('Belum ada gerakan stok')
            ->striped()
            ->searchable(false)
            ->paginated(false);
    }
}
