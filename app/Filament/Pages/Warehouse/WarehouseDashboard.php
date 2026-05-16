<?php

namespace App\Filament\Pages\Warehouse;

use App\Filament\Widgets\Owner\OwnerLowStockTable;
use App\Filament\Widgets\ResourceStats\InventoryLogStatsWidget;
use App\Filament\Widgets\ResourceStats\ProductStatsWidget;
use App\Filament\Widgets\Warehouse\WarehouseStockMovementChart;
use Filament\Pages\Dashboard as BaseDashboard;

class WarehouseDashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard Gudang';

    public function getWidgets(): array
    {
        return [
            ProductStatsWidget::class,
            WarehouseStockMovementChart::class,
            OwnerLowStockTable::class,
            InventoryLogStatsWidget::class,
        ];
    }
}
