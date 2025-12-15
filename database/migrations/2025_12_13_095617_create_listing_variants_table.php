<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_variants', function (Blueprint $table) {
            $table->id(); // This creates BIGINT UNSIGNED
            $table->unsignedBigInteger('listing_id'); // Explicitly match listings.id type
            $table->string('sku')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->json('attributes')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Add foreign key constraint
            $table->foreign('listing_id')
                  ->references('id')
                  ->on('listings')
                  ->onDelete('cascade');
            
            // Add unique constraint
            $table->unique(['listing_id', 'sku'], 'listing_variant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_variants');
    }
};