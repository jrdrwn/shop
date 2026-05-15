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
        Schema::table('tokos', function (Blueprint $table) {
            $table->boolean('enable_cash')->default(true)->after('is_active');
            $table->boolean('enable_debit')->default(true)->after('enable_cash');
            $table->boolean('enable_qris')->default(true)->after('enable_debit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->dropColumn(['enable_cash', 'enable_debit', 'enable_qris']);
        });
    }
};
