<?php

namespace Database\Seeders;

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

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // prevent duplicate run
        $existingSuperAdmin = User::where('email', 'admin@example.com')->first();

        if ($existingSuperAdmin) {
            if ($existingSuperAdmin->role !== 'super_admin') {
                $existingSuperAdmin->update(['role' => 'super_admin']);
            }

            return;
        }

        DB::transaction(function () {
            // Super Admin
            $admin = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'phone' => '08123456789',
                'is_active' => true,
            ]);

            // Toko
            $toko = Toko::create([
                'name' => 'Toko Sample',
                'address' => 'Jl. Contoh No.1',
                'phone' => '021888999',
                'email' => 'toko@example.com',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'description' => 'Sample toko for testing',
                'owner_name' => 'Owner Name',
                'is_active' => true,
                'created_by' => $admin->id,
            ]);

            // Owner
            $Owner = User::create([
                'name' => 'Toko Owner',
                'email' => 'Owner@example.com',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'phone' => '08112223344',
                'store_id' => $toko->id,
                'is_active' => true,
            ]);

            TokoOwner::create([
                'store_id' => $toko->id,
                'owner_id' => $Owner->id,
                'assigned_at' => now(),
                'assigned_by' => $admin->id,
            ]);

            // Products
            // Create default category
            $category = Category::create([
                'store_id' => $toko->id,
                'name' => 'Uncategorized',
                'description' => 'Default category',
            ]);

            $products = [
                ['name' => 'Espresso', 'sku' => 'ESP001', 'price' => 15000, 'stock' => 50],
                ['name' => 'Cappuccino', 'sku' => 'CAP001', 'price' => 20000, 'stock' => 40],
                ['name' => 'Latte', 'sku' => 'LAT001', 'price' => 20000, 'stock' => 30],
            ];

            foreach ($products as $p) {
                Product::create(array_merge($p, [
                    'store_id' => $toko->id,
                    'category_id' => $category->id,
                    'is_active' => true,
                ]));
            }

            // Payment methods
            $methods = ['Cash' => 'cash', 'Debit' => 'debit', 'QRIS' => 'qris'];
            foreach ($methods as $name => $type) {
                PaymentMethod::create([
                    'store_id' => $toko->id,
                    'name' => $name,
                    'type' => $type,
                    'is_active' => true,
                ]);
            }

            // Subscription sample
            Subscription::create([
                'name' => 'Free Plan',
                'price' => 0,
                'duration_months' => 0,
                'features' => ['basic_pos', 'product_management'],
                'is_active' => true,
            ]);

            Subscription::create([
                'name' => 'Medium Plan',
                'price' => 150000,
                'duration_months' => 1,
                'features' => ['advanced_reports', 'inventory_management'],
                'is_active' => true,
            ]);

            Subscription::create([
                'name' => 'Premium Plan',
                'price' => 200000,
                'duration_months' => 1,
                'features' => ['unlimited_stores', 'all_features'],
                'is_active' => true,
            ]);
        });
    }
}
