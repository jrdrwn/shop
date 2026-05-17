<?php

namespace App\Filament\Pages\Warehouse;

use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;

class WarehouseLogin extends Login
{
    public function getHeading(): string
    {
        return 'Login Gudang';
    }

    public function getSubheading(): ?string
    {
        return 'Panel Gudang - Kelola Stok Barang';
    }

    protected function getRedirectUrl(): string
    {
        return Filament::getUrl();
    }
}
