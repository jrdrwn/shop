<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\Login;

class AdminLogin extends Login
{
    public function getHeading(): string
    {
        return 'Login Admin';
    }

    public function getSubheading(): ?string
    {
        return 'Panel Admin - Kelola Semua Cafe';
    }
}
