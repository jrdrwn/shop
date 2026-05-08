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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'manager', 'cashier'])->default('cashier')->after('password');
            $table->string('phone')->nullable()->after('role');
            $table->unsignedBigInteger('cafe_id')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('cafe_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'cafe_id', 'is_active']);
        });
    }
};
