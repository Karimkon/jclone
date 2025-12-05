<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('import_costs')) {
            Schema::create('import_costs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('import_request_id')->constrained('import_requests')->onDelete('cascade');
                
                // CIF breakdown
                $table->decimal('item_cost', 15, 2)->default(0);
                $table->decimal('freight', 15, 2)->default(0);
                $table->decimal('insurance', 15, 2)->default(0);
                $table->decimal('cif', 15, 2)->default(0);

                // Taxes
                $table->decimal('duty', 15, 2)->default(0);
                $table->decimal('vat', 15, 2)->default(0);
                $table->decimal('other_taxes', 15, 2)->default(0);
                $table->decimal('total_tax', 15, 2)->default(0);

                // Commissions
                $table->decimal('import_commission', 15, 2)->default(0);
                $table->decimal('platform_commission', 15, 2)->default(0);

                // Final totals
                $table->decimal('final_import_cost', 15, 2)->default(0);
                $table->json('breakdown')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('import_costs');
    }
};