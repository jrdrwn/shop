<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// NOTE: Originally added midtrans_* fields. Those columns were removed in
// 2026_07_25_041823_drop_midtrans_fields_from_tokos_table.php.
// This migration now only handles the qris_type column.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tokos', 'qris_type')) {
            Schema::table('tokos', function (Blueprint $table) {
                $table->string('qris_type')->default('manual')->after('logo_url');
            });
        }
    }

    public function down(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->dropColumn('qris_type');
        });
    }
};
