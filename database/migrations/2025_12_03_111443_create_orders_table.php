<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->enum('status', ['pending','paid','processing','shipped','delivered','cancelled','refunded'])->default('pending');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('shipping', 15, 2)->default(0);
            $table->decimal('taxes', 15, 2)->default(0);
            $table->decimal('platform_commission', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('listing_id')->nullable()->constrained('listings')->onDelete('set null');
            $table->string('title');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->json('attributes')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('provider'); // flutterwave or pesapal
            $table->string('provider_payment_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('status', ['pending','completed','failed','refunded'])->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('escrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('status', ['held','released','refunded'])->default('held');
            $table->timestamp('release_at')->nullable(); // auto release fallback
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('escrows');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
