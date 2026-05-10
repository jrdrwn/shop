<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['cafe_id'] = Auth::user()?->cafe_id;

        if (! ($data['has_variants'] ?? false)) {
            $data['variants'] = null;
        }

        return $data;
    }
}
