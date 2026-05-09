<?php

namespace App\Filament\Resources\Cafes\Pages;

use App\Filament\Resources\Cafes\CafeResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCafes extends ListRecords
{
    protected static string $resource = CafeResource::class;

    protected function getHeaderActions(): array
    {
        // canCreate() returns false for all roles — no New button
        return [];
    }

    public function mount(): void
    {
        parent::mount();

        // Manager only has one cafe — redirect directly to its view page
        if (Auth::user()?->role === 'manager') {
            $query = static::getResource()::getEloquentQuery();

            if ($query->count() === 1) {
                $record = $query->first();

                if ($record) {
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record->getKey()]));
                }
            }
        }
    }
}
