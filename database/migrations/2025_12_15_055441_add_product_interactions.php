<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if columns don't exist before adding them
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'view_count')) {
                $table->integer('view_count')->default(0)->after('stock');
            }
            if (!Schema::hasColumn('listings', 'click_count')) {
                $table->integer('click_count')->default(0)->after('view_count');
            }
            if (!Schema::hasColumn('listings', 'wishlist_count')) {
                $table->integer('wishlist_count')->default(0)->after('click_count');
            }
            if (!Schema::hasColumn('listings', 'cart_add_count')) {
                $table->integer('cart_add_count')->default(0)->after('wishlist_count');
            }
            if (!Schema::hasColumn('listings', 'purchase_count')) {
                $table->integer('purchase_count')->default(0)->after('cart_add_count');
            }
            if (!Schema::hasColumn('listings', 'share_count')) {
                $table->integer('share_count')->default(0)->after('purchase_count');
            }
            if (!Schema::hasColumn('listings', 'last_viewed_at')) {
                $table->timestamp('last_viewed_at')->nullable()->after('share_count');
            }
        });

        // Create product_analytics table if it doesn't exist
        if (!Schema::hasTable('product_analytics')) {
            Schema::create('product_analytics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('listing_id')->constrained('listings')->onDelete('cascade');
                $table->date('date')->index();
                $table->integer('views')->default(0);
                $table->integer('clicks')->default(0);
                $table->integer('add_to_cart')->default(0);
                $table->integer('add_to_wishlist')->default(0);
                $table->integer('purchases')->default(0);
                $table->integer('shares')->default(0);
                $table->decimal('conversion_rate', 5, 2)->default(0);
                $table->decimal('cart_abandon_rate', 5, 2)->default(0);
                $table->json('top_sources')->nullable();
                $table->timestamps();
                
                $table->unique(['listing_id', 'date']);
            });
        }
    }

   public function down(): void
{
    Schema::table('listings', function (Blueprint $table) {
        $columns = [
            'view_count',
            'click_count',
            'wishlist_count',
            'cart_add_count',
            'purchase_count',
            'share_count',
            'last_viewed_at',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('listings', $column)) {
                $table->dropColumn($column);
            }
        }
    });

    Schema::dropIfExists('product_analytics');
}

};