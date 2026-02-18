<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('push_enabled')->default(true);
            $table->boolean('email_enabled')->default(true);
            // Buyer preferences
            $table->boolean('order_updates')->default(true);
            $table->boolean('promotions')->default(true);
            $table->boolean('recommendations')->default(true);
            $table->boolean('price_drops')->default(true);
            $table->boolean('cart_reminders')->default(true);
            // Vendor preferences
            $table->boolean('new_orders')->default(true);
            $table->boolean('reviews')->default(true);
            $table->boolean('payouts')->default(true);
            $table->boolean('vendor_tips')->default(false);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};
