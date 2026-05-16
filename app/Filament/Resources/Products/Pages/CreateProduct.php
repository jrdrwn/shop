<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\Toko;
use App\Services\SubscriptionService;
use Illuminate\Validation\ValidationException;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if (in_array($user?->role, ['owner', 'gudang'], true) && filled($user->toko_id)) {
            $toko = Toko::find($user->toko_id);
            if ($toko) {
                $service = app(SubscriptionService::class);
                if (! $service->canCreateProduct($toko)) {
                    throw ValidationException::withMessages([
                        'name' => ['Batas produk tercapai. Upgrade paket untuk menambah produk.'],
                    ]);
                }
            }
        }

        $data['toko_id'] = $user?->toko_id;

        if (! ($data['has_variants'] ?? false)) {
            $data['variants'] = null;
        }

        return $data;
    }
}
