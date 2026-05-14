<?php

namespace App\Filament\Resources\InventoryLogs\Pages;

use App\Filament\Resources\InventoryLogs\InventoryLogResource;
use App\Filament\Widgets\ResourceStats\InventoryLogStatsWidget;
use Filament\Resources\Pages\ListRecords;

class ListInventoryLogs extends ListRecords
{
    protected static string $resource = InventoryLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InventoryLogStatsWidget::class,
        ];
    }
}
