<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cafe_id')->constrained('cafes')->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('restrict');
            $table->string('order_id')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending'); // pending, success, failed, expire, cancel
            $table->string('payment_type')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('transaction_time')->nullable();
            $table->timestamp('settlement_time')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['cafe_id', 'status']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
