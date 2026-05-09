<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        $role = Auth::user()?->role;

        if (is_string($role) && in_array($role, ['super_admin'], true)) {
            return [
                CreateAction::make(),
            ];
        }

        return [];
    }
}
