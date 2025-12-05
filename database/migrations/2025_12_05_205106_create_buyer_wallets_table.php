<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('buyer_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('locked_balance', 15, 2)->default(0); // For pending orders
            $table->string('currency', 10)->default('USD');
            $table->json('meta')->nullable();
            $table->timestamps();
            
            $table->unique('user_id');
        });
        
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // deposit, withdrawal, payment, refund, commission, bonus
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('reference')->nullable();
            $table->string('status')->default('completed'); // pending, completed, failed
            $table->text('description')->nullable();
            $table->json('meta')->nullable(); // payment method, order_id, etc
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index('reference');
        });
        
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->json('items'); // {listing_id, quantity, price, etc}
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('shipping', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'session_id']);
        });
        
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->json('meta')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'listing_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('buyer_wallets');
    }
};