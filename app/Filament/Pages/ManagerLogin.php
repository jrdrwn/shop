<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\Login;

class ManagerLogin extends Login
{
    public function getHeading(): string
    {
        return 'Login Manajer';
    }

    // Removed getSubheading() override to allow register action to show
}
