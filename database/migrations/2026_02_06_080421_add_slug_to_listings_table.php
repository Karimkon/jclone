<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
        });

        // Generate slugs for existing listings
        $listings = \DB::table('listings')->get();
        foreach ($listings as $listing) {
            $baseSlug = Str::slug($listing->title);
            $slug = $baseSlug;
            $counter = 1;

            // Ensure unique slug
            while (\DB::table('listings')->where('slug', $slug)->where('id', '!=', $listing->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            \DB::table('listings')->where('id', $listing->id)->update(['slug' => $slug]);
        }

        // Make slug required after populating
        Schema::table('listings', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
