<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->string('doku_client_id')->nullable()->after('qris_type');
            $table->string('doku_secret_key')->nullable()->after('doku_client_id');
            $table->boolean('doku_is_production')->default(false)->after('doku_secret_key');
        });
    }

    public function down(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->dropColumn([
                'doku_client_id',
                'doku_secret_key',
                'doku_is_production',
            ]);
        });
    }
};
