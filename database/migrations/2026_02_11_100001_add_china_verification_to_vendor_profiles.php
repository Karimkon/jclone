<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->string('uscc', 18)->nullable()->after('preferred_currency')
                  ->comment('Unified Social Credit Code for China suppliers');
            $table->index('uscc');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropIndex(['uscc']);
            $table->dropColumn('uscc');
        });
    }
};
