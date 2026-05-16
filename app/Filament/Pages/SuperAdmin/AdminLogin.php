<?php

namespace App\Filament\Pages\SuperAdmin;

use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;

class AdminLogin extends Login
{
    public function getHeading(): string
    {
        return 'Login Admin';
    }

    public function getSubheading(): ?string
    {
        return 'Panel Admin - Kelola Semua Toko';
    }

    protected function getRedirectUrl(): string
    {
        return Filament::getUrl();
    }
}
