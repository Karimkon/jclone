<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Drop the old FK so we can make vendor_profile_id nullable
            $table->dropForeign(['vendor_profile_id']);

            // Make vendor_profile_id nullable — support chats have no vendor
            $table->unsignedBigInteger('vendor_profile_id')->nullable()->change();

            // Re-add the FK as nullable
            $table->foreign('vendor_profile_id')
                  ->references('id')
                  ->on('vendor_profiles')
                  ->onDelete('cascade');

            // Flag to distinguish support conversations from buyer-vendor ones
            $table->boolean('is_support_chat')->default(false)->after('subject');

            // The admin/support user handling this conversation
            $table->unsignedBigInteger('support_user_id')->nullable()->after('is_support_chat');
            $table->foreign('support_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['support_user_id']);
            $table->dropColumn('support_user_id');
            $table->dropColumn('is_support_chat');

            $table->dropForeign(['vendor_profile_id']);
            $table->unsignedBigInteger('vendor_profile_id')->nullable(false)->change();
            $table->foreign('vendor_profile_id')
                  ->references('id')
                  ->on('vendor_profiles')
                  ->onDelete('cascade');
        });
    }
};
