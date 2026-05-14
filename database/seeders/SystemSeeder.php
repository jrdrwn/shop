<?php

namespace Database\Seeders;

use App\Enums\SubscriptionPlan;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Toko;
use App\Models\TokoOwner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SystemSeeder extends Seeder
{
    public function run(): void
    {
        // Prevent duplicate run for admin
        $existingSuperAdmin = User::where('email', 'admin@example.com')->first();
        if ($existingSuperAdmin && $existingSuperAdmin->role !== 'super_admin') {
            $existingSuperAdmin->update(['role' => 'super_admin']);
        }

        DB::transaction(function () use ($existingSuperAdmin) {
            // Super Admin
            $admin = $existingSuperAdmin ?? User::create([
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'phone' => '08123456789',
                'is_active' => true,
            ]);

            // Create basic subscription plans based on Enum
            foreach (SubscriptionPlan::cases() as $plan) {
                Subscription::firstOrCreate(
                    ['plan' => $plan->value],
                    [
                        'name' => $plan->getLabel(),
                        'price' => $plan->price(),
                        'duration_months' => $plan->durationMonths(),
                        'features' => $plan->marketingFeatures(),
                        'is_active' => true,
                    ]
                );
            }

            $freePlan = Subscription::where('plan', SubscriptionPlan::Free->value)->first();

            // Toko
            $toko = Toko::firstOrCreate(['email' => 'toko@example.com'], [
                'name' => 'Toko Utama',
                'address' => 'Jl. Kemerdekaan No.1',
                'phone' => '021888999',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'description' => 'Sistem Utama Toko',
                'owner_name' => 'Bapak Owner',
                'is_active' => true,
                'created_by' => $admin->id,
                'subscription_id' => $freePlan->id ?? null,
                'tax_percentage' => 11,
                'service_charge_percentage' => 5,
            ]);

            // Owner
            $owner = User::firstOrCreate(['email' => 'owner@example.com'], [
                'name' => 'Bapak Owner',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'phone' => '08112223344',
                'toko_id' => $toko->id,
                'is_active' => true,
            ]);

            TokoOwner::firstOrCreate([
                'toko_id' => $toko->id,
                'owner_id' => $owner->id,
            ], [
                'assigned_at' => now(),
                'assigned_by' => $admin->id,
            ]);

            // Kasir (Cashier)
            $kasir = User::firstOrCreate(['email' => 'kasir@example.com'], [
                'name' => 'Mbak Kasir',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'phone' => '085566778899',
                'toko_id' => $toko->id,
                'is_active' => true,
            ]);

            // Gudang (Warehouse)
            $gudang = User::firstOrCreate(['email' => 'gudang@example.com'], [
                'name' => 'Mas Gudang',
                'password' => Hash::make('password'),
                'role' => 'gudang',
                'phone' => '083344556677',
                'toko_id' => $toko->id,
                'is_active' => true,
            ]);

            // Products Category
            $kategoriMinuman = Category::firstOrCreate(['name' => 'Minuman', 'toko_id' => $toko->id], [
                'description' => 'Minuman segar dan kopi',
            ]);
            
            $kategoriMakanan = Category::firstOrCreate(['name' => 'Makanan', 'toko_id' => $toko->id], [
                'description' => 'Makanan ringan dan berat',
            ]);

            // Products
            $products = [
                ['name' => 'Kopi Gula Aren', 'sku' => 'KOP001', 'price' => 18000, 'stock' => 100, 'category_id' => $kategoriMinuman->id],
                ['name' => 'Matcha Latte', 'sku' => 'MAT001', 'price' => 25000, 'stock' => 50, 'category_id' => $kategoriMinuman->id],
                ['name' => 'Roti Bakar Coklat', 'sku' => 'ROT001', 'price' => 15000, 'stock' => 30, 'category_id' => $kategoriMakanan->id],
            ];

            foreach ($products as $p) {
                Product::firstOrCreate(
                    ['sku' => $p['sku'], 'toko_id' => $toko->id], 
                    array_merge($p, [
                        'is_active' => true,
                        'has_variants' => false,
                        'variants' => [],
                    ])
                );
            }

            // Payment methods
            $methods = ['Tunai' => 'cash', 'Debit/Kredit' => 'debit', 'QRIS' => 'qris'];
            foreach ($methods as $name => $type) {
                PaymentMethod::firstOrCreate(
                    ['name' => $name, 'toko_id' => $toko->id], 
                    [
                        'type' => $type,
                        'is_active' => true,
                    ]
                );
            }
        });
    }
}
