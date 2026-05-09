<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\CashierPanelProvider;
use App\Providers\Filament\ManagerPanelProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    AdminPanelProvider::class,
    CashierPanelProvider::class,
    ManagerPanelProvider::class,
    FortifyServiceProvider::class,
];
