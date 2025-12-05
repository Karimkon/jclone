<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('vendor_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->decimal('score', 5, 2)->default(0);
            $table->json('factors')->nullable();
            $table->timestamps();
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->foreignId('listing_id')->nullable()->constrained('listings')->onDelete('set null');
            $table->enum('type', ['flash_sale','auction','featured','actioning']);
            $table->decimal('fee', 15, 2)->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('raised_by')->constrained('users')->onDelete('cascade');
            $table->text('reason');
            $table->enum('status', ['open','under_review','resolved','rejected'])->default('open');
            $table->json('evidence')->nullable();
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('vendor_scores');
    }
};
