<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('listing_images', function (Blueprint $table) {
            $table->enum('type', ['image', 'video'])->default('image')->after('path');
            $table->json('metadata')->nullable()->after('type');
        });
    }

    public function down()
    {
        Schema::table('listing_images', function (Blueprint $table) {
            $table->dropColumn(['type', 'metadata']);
        });
    }
};