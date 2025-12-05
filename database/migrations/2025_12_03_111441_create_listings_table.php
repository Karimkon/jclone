<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('weight_kg', 10, 2)->nullable();
            $table->enum('origin', ['local','imported'])->default('local');
            $table->enum('condition', ['new','used'])->default('new');
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('stock')->default(0);
            $table->json('attributes')->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('listings'); }
};
