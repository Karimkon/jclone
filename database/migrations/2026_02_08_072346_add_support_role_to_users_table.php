<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer','vendor_local','vendor_international','admin','support','logistics','finance','ceo') NOT NULL DEFAULT 'buyer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer','vendor_local','vendor_international','admin','logistics','finance','ceo') NOT NULL DEFAULT 'buyer'");
    }
};
