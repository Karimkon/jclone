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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Free, Bronze, Silver, Gold
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->decimal('boost_multiplier', 3, 2)->default(1.00); // 1.0, 1.5, 2.0, 3.0
            $table->integer('max_featured_listings')->default(0);
            $table->boolean('badge_enabled')->default(false);
            $table->string('badge_text')->nullable(); // "Bronze Seller", etc.
            $table->json('features')->nullable(); // Additional features JSON
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
