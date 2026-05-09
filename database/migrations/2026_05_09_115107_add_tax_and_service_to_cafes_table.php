<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cafes', function (Blueprint $table) {
            $table->unsignedTinyInteger('tax_percentage')->default(0)->after('logo_url')
                ->comment('Tax rate in percent, e.g. 11 = 11%');
            $table->unsignedTinyInteger('service_charge_percentage')->default(0)->after('tax_percentage')
                ->comment('Service charge in percent, e.g. 5 = 5%');
        });
    }

    public function down(): void
    {
        Schema::table('cafes', function (Blueprint $table) {
            $table->dropColumn(['tax_percentage', 'service_charge_percentage']);
        });
    }
};
