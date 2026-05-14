<?php

namespace App\Filament\Resources\TokoOwners\Pages;

use App\Filament\Resources\TokoOwners\TokoOwnerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListTokoOwners extends ListRecords
{
    protected static string $resource = TokoOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $user = Auth::user();

        // For owner users, if there's only one toko owner record for them,
        // redirect to the detail page for that record to simplify the flow.
        if ($user?->role === 'owner') {
            $query = static::getResource()::getEloquentQuery();
            $count = $query->count();

            if ($count === 1) {
                $record = $query->first();
                if ($record) {
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record->getKey()]));
                }
            }
        }
    }
}
