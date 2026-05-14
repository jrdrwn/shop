<?php

namespace App\Filament\Resources\CashFlows\Pages;

use App\Filament\Resources\CashFlows\CashFlowResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCashFlow extends CreateRecord
{
    protected static string $resource = CashFlowResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if (! isset($data['toko_id']) || empty($data['toko_id'])) {
            $data['toko_id'] = $user?->toko_id;
        }

        $data['created_by'] = $user?->id;

        return $data;
    }
}
