<?php

namespace App\Filament\Resources\TokoOwners\Pages;

use App\Filament\Resources\TokoOwners\TokoOwnerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTokoOwner extends ViewRecord
{
    protected static string $resource = TokoOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
