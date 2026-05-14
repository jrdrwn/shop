<?php

namespace Database\Seeders;

use App\Models\Toko;
use Illuminate\Database\Seeder;

class DefaultPaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tokos = Toko::whereDoesntHave('paymentMethods')->get();

        foreach ($tokos as $toko) {
            $toko->paymentMethods()->createMany([
                ['name' => 'Tunai', 'type' => 'cash', 'is_active' => true],
                ['name' => 'QRIS', 'type' => 'qris', 'is_active' => true],
            ]);
        }
    }
}
