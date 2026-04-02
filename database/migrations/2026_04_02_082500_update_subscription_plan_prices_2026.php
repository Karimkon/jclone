<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update subscription plan prices to:
     * Bronze: 15,000 UGX (unchanged)
     * Silver: 25,000 UGX (was 45,000)
     * Gold:   40,000 UGX (was 100,000)
     */
    public function up(): void
    {
        DB::table('subscription_plans')->where('slug', 'silver')->update(['price' => 25000]);
        DB::table('subscription_plans')->where('slug', 'gold')->update(['price' => 40000]);
    }

    public function down(): void
    {
        DB::table('subscription_plans')->where('slug', 'silver')->update(['price' => 45000]);
        DB::table('subscription_plans')->where('slug', 'gold')->update(['price' => 100000]);
    }
};
