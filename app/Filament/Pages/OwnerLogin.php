<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\Login;

class OwnerLogin extends Login
{
    public function getHeading(): string
    {
        return 'Login Owner';
    }

    // Removed getSubheading() override to allow register action to show
}
