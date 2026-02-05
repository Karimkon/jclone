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
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_subscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_profile_id')->constrained()->onDelete('cascade');
            $table->string('pesapal_order_tracking_id')->nullable();
            $table->string('pesapal_merchant_reference')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('UGX');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->json('payment_response')->nullable();
            $table->timestamps();

            $table->index('pesapal_merchant_reference');
            $table->index('pesapal_order_tracking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
