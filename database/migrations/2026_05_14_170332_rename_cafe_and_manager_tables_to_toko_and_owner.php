<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename tables
        if (Schema::hasTable('stores')) {
            Schema::rename('stores', 'tokos');
        }

        if (Schema::hasTable('cafe_managers')) {
            Schema::rename('cafe_managers', 'toko_owners');
        }

        // 2. Rename columns in toko_owners (formerly cafe_managers)
        if (Schema::hasTable('toko_owners')) {
            Schema::table('toko_owners', function (Blueprint $table) {
                if (Schema::hasColumn('toko_owners', 'manager_id')) {
                    $table->renameColumn('manager_id', 'owner_id');
                }
                if (Schema::hasColumn('toko_owners', 'store_id')) {
                    $table->renameColumn('store_id', 'toko_id');
                }
            });
        }

        // 3. Rename store_id or cafe_id to toko_id in all related tables
        $tables = [
            'categories', 'products', 'payment_methods', 'transactions',
            'transaction_history', 'inventory_logs', 'daily_reports',
            'users', 'suppliers', 'stock_movements', 'cash_flows',
            'activity_logs', 'subscription_payments',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'store_id')) {
                        $table->renameColumn('store_id', 'toko_id');
                    } elseif (Schema::hasColumn($tableName, 'cafe_id')) {
                        $table->renameColumn('cafe_id', 'toko_id');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse column renaming
        $tables = [
            'categories', 'products', 'payment_methods', 'transactions',
            'transaction_history', 'inventory_logs', 'daily_reports',
            'users', 'suppliers', 'stock_movements', 'cash_flows',
            'activity_logs', 'subscription_payments',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'toko_id')) {
                        $table->renameColumn('toko_id', 'store_id');
                    }
                });
            }
        }

        // Reverse table renaming
        if (Schema::hasTable('toko_owners')) {
            Schema::table('toko_owners', function (Blueprint $table) {
                if (Schema::hasColumn('toko_owners', 'owner_id')) {
                    $table->renameColumn('owner_id', 'manager_id');
                }
            });
            Schema::rename('toko_owners', 'cafe_managers');
        }

        if (Schema::hasTable('tokos')) {
            Schema::rename('tokos', 'stores');
        }
    }
};
