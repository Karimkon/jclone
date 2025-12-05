<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('vendor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('vendor_type', ['local_retail','china_supplier','dropship']);
            $table->string('business_name')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->decimal('annual_turnover', 15, 2)->nullable();
            $table->string('preferred_currency', 3)->default('USD');
            $table->enum('vetting_status', ['pending','approved','rejected','manual_review'])->default('pending');
            $table->text('vetting_notes')->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('vendor_profiles'); }
};
