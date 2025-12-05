<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->unique();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->enum('role', ['buyer','vendor_local','vendor_international','admin','logistics','clearing_agent'])->default('buyer');
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable(); // extra JSON for profile info
            $table->rememberToken();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('users'); }
};
