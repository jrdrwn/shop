<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\InventoryLog;
use Filament\Resources\Pages\CreateRecord;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if ($user && filled($user->toko_id)) {
            $data['toko_id'] = $user->toko_id;
        }

        $data['created_by'] = $user?->id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $movement = $this->record;
        $product = $movement->product;

        if ($product) {
            // Update product stock
            $product->stock = $movement->quantity_after;
            $product->save();

            // Create InventoryLog
            InventoryLog::create([
                'toko_id' => $movement->toko_id,
                'product_id' => $movement->product_id,
                'action' => 'Stock Movement: '.$movement->movement_type,
                'quantity_change' => $movement->quantity_change,
                'quantity_before' => $movement->quantity_before,
                'quantity_after' => $movement->quantity_after,
                'reference_id' => $movement->id,
                'reference_type' => 'StockMovement',
                'notes' => $movement->notes,
                'created_by' => $movement->created_by,
            ]);
        }
    }
}
