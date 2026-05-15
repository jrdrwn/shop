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
            $table->dropColumn(['enable_cash', 'enable_debit', 'enable_qris']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->boolean('enable_cash')->default(true);
            $table->boolean('enable_debit')->default(true);
            $table->boolean('enable_qris')->default(true);
        });
    }
};
