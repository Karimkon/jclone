<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Phone OTP fields for SMS verification
            $table->string('phone_otp_code', 6)->nullable()->after('otp_expires_at');
            $table->timestamp('phone_otp_expires_at')->nullable()->after('phone_otp_code');
            $table->boolean('phone_verified')->default(false)->after('phone_otp_expires_at');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_otp_code',
                'phone_otp_expires_at',
                'phone_verified',
                'phone_verified_at',
            ]);
        });
    }
};
