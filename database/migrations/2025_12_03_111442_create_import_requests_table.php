<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('import_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->nullable()->constrained('listings')->onDelete('set null');
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->decimal('supplier_price', 15, 2)->default(0);
            $table->decimal('freight', 15, 2)->default(0);
            $table->decimal('insurance', 15, 2)->default(0);
            $table->enum('calc_method', ['ad_valorem','weight'])->default('ad_valorem');
            $table->decimal('weight_kg', 10, 2)->nullable();
            $table->json('tariff_meta')->nullable();
            $table->enum('status', ['draft','pending','importing','cleared','delivered','cancelled'])->default('draft');
            $table->timestamps();
        });

       Schema::create('import_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_request_id')->constrained()->onDelete('cascade');
            
            // CIF breakdown
            $table->decimal('item_cost', 12, 2)->default(0);
            $table->decimal('freight', 12, 2)->default(0);
            $table->decimal('insurance', 12, 2)->default(0);
            $table->decimal('cif', 12, 2)->default(0);

            // Taxes
            $table->decimal('duty', 12, 2)->default(0);
            $table->decimal('vat', 12, 2)->default(0);
            $table->decimal('other_taxes', 12, 2)->default(0);
            $table->decimal('total_tax', 12, 2)->default(0);

            // Commissions
            $table->decimal('import_commission', 12, 2)->default(0);
            $table->decimal('platform_commission', 12, 2)->default(0);

            // Final totals
            $table->decimal('final_import_cost', 12, 2)->default(0);
            $table->json('breakdown')->nullable();

            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('import_costs');
        Schema::dropIfExists('import_requests');
    }
};
