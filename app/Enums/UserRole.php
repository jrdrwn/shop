<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Owner = 'owner';
    case Cashier = 'kasir';
    case Warehouse = 'gudang';
}
