<?php

namespace App\Filament\Resources\CashFlows\Pages;

use App\Filament\Resources\CashFlows\CashFlowResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCashFlows extends ListRecords
{
    protected static string $resource = CashFlowResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ResourceStats\CashFlowStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
