<?php

namespace Database\Seeders;

use App\Models\Cafe;
use App\Models\CafeManager;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Subscription;
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

            // Cafe
            $cafe = Cafe::create([
                'name' => 'Cafe Sample',
                'address' => 'Jl. Contoh No.1',
                'phone' => '021888999',
                'email' => 'cafe@example.com',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'description' => 'Sample cafe for testing',
                'owner_name' => 'Owner Name',
                'is_active' => true,
                'created_by' => $admin->id,
            ]);

            // Manager
            $manager = User::create([
                'name' => 'Cafe Manager',
                'email' => 'manager@example.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'phone' => '08112223344',
                'cafe_id' => $cafe->id,
                'is_active' => true,
            ]);

            CafeManager::create([
                'cafe_id' => $cafe->id,
                'manager_id' => $manager->id,
                'assigned_at' => now(),
                'assigned_by' => $admin->id,
            ]);

            // Products
            // Create default category
            $category = Category::create([
                'cafe_id' => $cafe->id,
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
                    'cafe_id' => $cafe->id,
                    'category_id' => $category->id,
                    'is_active' => true,
                ]));
            }

            // Payment methods
            $methods = ['Cash' => 'cash', 'Debit' => 'debit', 'QRIS' => 'qris'];
            foreach ($methods as $name => $type) {
                PaymentMethod::create([
                    'cafe_id' => $cafe->id,
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
                'name' => 'Pro Plan',
                'price' => 99000,
                'duration_months' => 1,
                'features' => ['advanced_reports', 'multiple-stores'],
                'is_active' => true,
            ]);
        });
    }
}
