<?php

namespace App\Filament\Pages\Cashier;

use Filament\Auth\Pages\Login;

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
}
