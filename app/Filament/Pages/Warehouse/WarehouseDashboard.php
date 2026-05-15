<?php

namespace App\Filament\Pages\Warehouse;

use Filament\Pages\Dashboard as BaseDashboard;

class WarehouseDashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard Gudang';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\ResourceStats\ProductStatsWidget::class,
            \App\Filament\Widgets\WarehouseStockMovementChart::class,
            \App\Filament\Widgets\OwnerLowStockTable::class,
            \App\Filament\Widgets\ResourceStats\InventoryLogStatsWidget::class,
        ];
    }
}
