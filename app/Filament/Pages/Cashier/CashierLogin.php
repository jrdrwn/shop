<?php

namespace App\Filament\Pages\Cashier;

use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;

class CashierLogin extends Login
{
    public function getHeading(): string
    {
        return 'Login Kasir';
    }

    public function getSubheading(): ?string
    {
        return 'Panel Kasir - Point of Sale';
    }

    protected function getRedirectUrl(): string
    {
        return Filament::getUrl();
    }
}
