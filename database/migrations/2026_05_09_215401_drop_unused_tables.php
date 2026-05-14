<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Keep 'cafe_managers' for backwards compatibility with tests and existing resources.
        // Schema::dropIfExists('cafe_managers');
        // Schema::dropIfExists('daily_reports');
        Schema::dropIfExists('transaction_history');
        Schema::dropIfExists('user_activity_logs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversibility is not required for cleaning up unused tables.
    }
};
