<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create categories table if it doesn't exist
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('image')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('parent_id')->nullable();
                $table->integer('order')->default(0);
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        // Check if listings table has category_id column
        if (Schema::hasTable('listings') && !Schema::hasColumn('listings', 'category_id')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        // Drop category_id from listings if it exists
        if (Schema::hasTable('listings') && Schema::hasColumn('listings', 'category_id')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
        
        // Drop categories table if it exists
        Schema::dropIfExists('categories');
    }
};