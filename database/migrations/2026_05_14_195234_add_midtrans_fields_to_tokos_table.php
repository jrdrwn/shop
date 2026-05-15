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
            $table->string('qris_type')->default('manual')->after('logo_url');
            $table->string('midtrans_client_key')->nullable()->after('qris_type');
            $table->string('midtrans_server_key')->nullable()->after('midtrans_client_key');
            $table->string('midtrans_merchant_id')->nullable()->after('midtrans_server_key');
            $table->boolean('midtrans_is_production')->default(false)->after('midtrans_merchant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->dropColumn([
                'qris_type',
                'midtrans_client_key',
                'midtrans_server_key',
                'midtrans_merchant_id',
                'midtrans_is_production'
            ]);
        });
    }
};
