<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('label')->nullable(); // e.g., "Home", "Office", "Friend's Place"
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('address_line_1');
            $table->text('address_line_2')->nullable();
            $table->string('city');
            $table->string('state_region')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Uganda');
            $table->text('delivery_instructions')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            // Ensure only one default address per user
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};