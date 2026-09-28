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
        Schema::table('tokos', function (Blueprint $table) {
            $table->string('ipaymu_va')->nullable()->after('qris_type');
            $table->text('ipaymu_api_key')->nullable()->after('ipaymu_va');
            $table->boolean('ipaymu_is_production')->default(false)->after('ipaymu_api_key');
        });

        DB::table('tokos')->where('qris_type', 'doku')->update(['qris_type' => 'manual']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->dropColumn(['ipaymu_va', 'ipaymu_api_key', 'ipaymu_is_production']);
        });
    }
};
