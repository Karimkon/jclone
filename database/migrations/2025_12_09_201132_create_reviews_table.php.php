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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_profile_id')->constrained()->onDelete('cascade');
            
            $table->unsignedTinyInteger('rating'); // 1-5 stars
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            
            // Review attributes (quality, value, shipping, etc.)
            $table->unsignedTinyInteger('quality_rating')->nullable(); // 1-5
            $table->unsignedTinyInteger('value_rating')->nullable(); // 1-5
            $table->unsignedTinyInteger('shipping_rating')->nullable(); // 1-5
            
            // Review media
            $table->json('images')->nullable(); // Array of image paths
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'flagged'])->default('approved');
            
            // Vendor response
            $table->text('vendor_response')->nullable();
            $table->timestamp('vendor_responded_at')->nullable();
            
            // Moderation
            $table->boolean('is_verified_purchase')->default(true);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->unsignedInteger('unhelpful_count')->default(0);
            
            // Meta for additional data
            $table->json('meta')->nullable();
            
            $table->timestamps();
            
            // Ensure one review per user per order item
            $table->unique(['user_id', 'order_item_id'], 'unique_user_order_item_review');
            
            // Indexes for faster queries
            $table->index(['listing_id', 'status']);
            $table->index(['vendor_profile_id', 'status']);
            $table->index(['rating']);
        });
        
        // Table to track review helpfulness votes
        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('vote', ['helpful', 'unhelpful']);
            $table->timestamps();
            
            $table->unique(['review_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_votes');
        Schema::dropIfExists('reviews');
    }
};