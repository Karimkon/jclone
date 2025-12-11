<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates conversations and messages tables for the chat system
     */
    public function up(): void
    {
        // Conversations table - represents a chat thread between buyer and vendor
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->foreignId('listing_id')->nullable()->constrained('listings')->onDelete('set null'); // Optional: link to specific product
            $table->string('subject')->nullable(); // Optional subject/title for the conversation
            $table->timestamp('last_message_at')->nullable();
            $table->enum('status', ['active', 'archived', 'blocked'])->default('active');
            $table->timestamps();
            
            // Ensure unique conversation per buyer-vendor pair (optionally per listing)
            $table->unique(['buyer_id', 'vendor_profile_id', 'listing_id'], 'unique_conversation');
            
            // Indexes for quick lookups
            $table->index('buyer_id');
            $table->index('vendor_profile_id');
            $table->index('last_message_at');
        });

        // Messages table - individual messages within conversations
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('body');
            $table->enum('type', ['text', 'image', 'file', 'system'])->default('text');
            $table->string('attachment_path')->nullable(); // For images/files
            $table->string('attachment_name')->nullable(); // Original filename
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            
            // Indexes for efficient queries
            $table->index(['conversation_id', 'created_at']);
            $table->index('sender_id');
            $table->index('read_at');
        });

        // Message read receipts (for tracking who read what)
        Schema::create('message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('read_at');
            
            $table->unique(['message_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_reads');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
