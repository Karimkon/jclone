<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->decimal('tax_amount', 12, 2)->default(0)->after('price');
            $table->string('tax_description', 255)->nullable()->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['tax_amount', 'tax_description']);
        });
    }
};
