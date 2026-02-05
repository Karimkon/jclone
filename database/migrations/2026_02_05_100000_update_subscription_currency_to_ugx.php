<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update default currency to UGX
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->string('currency', 3)->default('UGX')->change();
        });

        // Update subscription plan prices to UGX
        DB::table('subscription_plans')->where('slug', 'bronze')->update(['price' => 15000]);
        DB::table('subscription_plans')->where('slug', 'silver')->update(['price' => 45000]);
        DB::table('subscription_plans')->where('slug', 'gold')->update(['price' => 100000]);

        // Update existing payment records to UGX
        DB::table('subscription_payments')->where('currency', 'KES')->update(['currency' => 'UGX']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->string('currency', 3)->default('KES')->change();
        });

        // Revert plan prices to KES values
        DB::table('subscription_plans')->where('slug', 'bronze')->update(['price' => 500]);
        DB::table('subscription_plans')->where('slug', 'silver')->update(['price' => 1500]);
        DB::table('subscription_plans')->where('slug', 'gold')->update(['price' => 3500]);

        // Revert currency back to KES
        DB::table('subscription_payments')->where('currency', 'UGX')->update(['currency' => 'KES']);
    }
};
