<?php

namespace App\Filament\Resources\TokoOwners\Pages;

use App\Filament\Resources\TokoOwners\TokoOwnerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTokoOwner extends EditRecord
{
    protected static string $resource = TokoOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
