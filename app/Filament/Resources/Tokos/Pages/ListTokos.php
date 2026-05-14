<?php

namespace App\Filament\Resources\Tokos\Pages;

use App\Filament\Resources\Tokos\TokoResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListTokos extends ListRecords
{
    protected static string $resource = TokoResource::class;

    protected function getHeaderActions(): array
    {
        // canCreate() returns false for all roles — no New button
        return [];
    }

    public function mount(): void
    {
        parent::mount();

        // Owner only has one toko — redirect directly to its view page
        if (Auth::user()?->role === 'owner') {
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
