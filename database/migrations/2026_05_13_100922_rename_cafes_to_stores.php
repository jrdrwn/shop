<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't support alter table rename, use raw SQL
            DB::statement('ALTER TABLE cafes RENAME TO stores');
        } else {
            // MySQL/PostgreSQL
            Schema::rename('cafes', 'stores');
        }

        // Update cafe_managers table
        if (Schema::hasTable('cafe_managers')) {
            Schema::table('cafe_managers', function ($table) {
                $table->renameColumn('cafe_id', 'store_id');
            });
        }

        // Rename cafe_id to store_id in other tables - simple column rename without FK drop
        $tables = [
            'categories', 'products', 'payment_methods', 'transactions',
            'transaction_history', 'inventory_logs', 'daily_reports',
            'users',
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'cafe_id')) {
                Schema::table($table, function ($tbl) {
                    $tbl->renameColumn('cafe_id', 'store_id');
                });
            }
        }

        // Rename cafe_id to store_id in new tables if they exist
        $newTables = ['suppliers', 'stock_movements', 'cash_flows', 'activity_logs'];
        foreach ($newTables as $table) {
            if (Schema::hasColumn($table, 'cafe_id')) {
                Schema::table($table, function ($tbl) {
                    $tbl->renameColumn('cafe_id', 'store_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is destructive, we won't support reverting the rename for safety
        throw new Exception('Cannot revert migration: rename_cafes_to_stores. Please restore from backup.');
    }
};
