<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $columns = ['midtrans_client_key', 'midtrans_server_key', 'midtrans_merchant_id', 'midtrans_is_production'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tokos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            if (! Schema::hasColumn('tokos', 'midtrans_client_key')) {
                $table->string('midtrans_client_key')->nullable()->after('qris_type');
                $table->string('midtrans_server_key')->nullable()->after('midtrans_client_key');
                $table->string('midtrans_merchant_id')->nullable()->after('midtrans_server_key');
                $table->boolean('midtrans_is_production')->default(false)->after('midtrans_merchant_id');
            }
        });
    }
};
