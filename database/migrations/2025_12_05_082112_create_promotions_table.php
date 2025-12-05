<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_profile_id')->constrained()->onDelete('cascade');
                $table->foreignId('listing_id')->nullable()->constrained()->onDelete('set null');
                $table->enum('type', ['flash_sale','auction','featured','actioning']);
                $table->decimal('fee', 15, 2)->default(0);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};