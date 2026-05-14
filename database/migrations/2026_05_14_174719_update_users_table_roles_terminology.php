<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'owner', 'kasir', 'gudang'])
                ->default('kasir')
                ->change();
        });

        // Update existing data
        DB::table('users')->where('role', 'manager')->update(['role' => 'owner']);
        DB::table('users')->where('role', 'cashier')->update(['role' => 'kasir']);
        DB::table('users')->where('role', 'warehouse')->update(['role' => 'gudang']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'manager', 'cashier'])
                ->default('cashier')
                ->change();
        });

        DB::table('users')->where('role', 'owner')->update(['role' => 'manager']);
        DB::table('users')->where('role', 'kasir')->update(['role' => 'cashier']);
        DB::table('users')->where('role', 'gudang')->update(['role' => 'warehouse']);
    }
};
