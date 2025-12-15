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
        Schema::create('vendor_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained()->onDelete('cascade');
            $table->integer('total_orders')->default(0);
            $table->integer('delivered_orders')->default(0);
            $table->integer('cancelled_orders')->default(0);
            $table->decimal('avg_delivery_time_days', 5, 2)->nullable();
            $table->decimal('avg_processing_time_hours', 5, 2)->nullable();
            $table->decimal('on_time_delivery_rate', 5, 2)->nullable();
            $table->decimal('delivery_score', 5, 2)->nullable();
            $table->decimal('response_time_score', 5, 2)->nullable();
            $table->json('metrics')->nullable();
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
            
            $table->index('vendor_profile_id');
            $table->index('delivery_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_performances');
    }
};