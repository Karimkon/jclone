<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('vendor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->string('type'); // bank_statement, national_id, proof_of_address, guarantor_id, company_docs
            $table->string('path');
            $table->string('mime')->nullable();
            $table->json('ocr_data')->nullable();
            $table->enum('status', ['uploaded','verified','rejected'])->default('uploaded');
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('vendor_documents'); }
};
