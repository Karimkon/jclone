<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * SIMPLIFIED: Vendors post jobs/services, Buyers apply/request
     */
    public function up(): void
    {
        // Service/Job Categories (shared for both)
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->enum('type', ['service', 'job', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('service_categories')->onDelete('set null');
        });

        // Jobs posted by Vendors
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('job_type', ['full_time', 'part_time', 'contract', 'freelance', 'internship', 'temporary'])->default('full_time');
            $table->enum('experience_level', ['entry', 'junior', 'mid', 'senior', 'expert'])->default('entry');
            $table->string('location')->nullable();
            $table->string('city')->default('Kampala');
            $table->boolean('is_remote')->default(false);
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->enum('salary_period', ['hourly', 'daily', 'weekly', 'monthly', 'yearly', 'project'])->default('monthly');
            $table->boolean('salary_negotiable')->default(true);
            $table->json('requirements')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('benefits')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->enum('application_method', ['email', 'phone', 'whatsapp', 'in_app'])->default('in_app');
            $table->date('deadline')->nullable();
            $table->integer('vacancies')->default(1);
            $table->integer('applications_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->enum('status', ['draft', 'active', 'paused', 'closed', 'filled'])->default('active');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // Job Applications from Buyers/Users
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_listing_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('cv_path')->nullable();
            $table->decimal('expected_salary', 12, 2)->nullable();
            $table->enum('status', ['pending', 'reviewed', 'shortlisted', 'interviewed', 'offered', 'hired', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['job_listing_id', 'user_id']);
        });

        // Services offered by Vendors
        Schema::create('vendor_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->string('slug');
            $table->text('description');
            $table->enum('pricing_type', ['fixed', 'hourly', 'negotiable', 'starting_from', 'free_quote'])->default('negotiable');
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('price_max', 12, 2)->nullable();
            $table->string('duration')->nullable();
            $table->string('location')->nullable();
            $table->string('city')->default('Kampala');
            $table->boolean('is_mobile')->default(false); // Can come to customer
            $table->json('features')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('views_count')->default(0);
            $table->integer('inquiries_count')->default(0);
            $table->integer('bookings_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['vendor_profile_id', 'slug']);
        });

        // Service Requests/Bookings from Buyers
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_service_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('request_number')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('description');
            $table->string('location')->nullable();
            $table->string('address')->nullable();
            $table->date('preferred_date')->nullable();
            $table->string('preferred_time')->nullable();
            $table->enum('urgency', ['normal', 'urgent', 'emergency'])->default('normal');
            $table->json('images')->nullable();
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->decimal('quoted_price', 12, 2)->nullable();
            $table->decimal('final_price', 12, 2)->nullable();
            $table->enum('status', [
                'pending',
                'quoted',
                'accepted',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('pending');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('vendor_notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // Service Reviews from Buyers
        Schema::create('service_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_service_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_request_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_profile_id')->constrained()->onDelete('cascade');
            $table->integer('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->json('images')->nullable();
            $table->text('vendor_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        // Service Inquiries (quick contact without booking)
        Schema::create('service_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_service_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('vendor_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('message');
            $table->enum('status', ['new', 'contacted', 'converted', 'closed'])->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_inquiries');
        Schema::dropIfExists('service_reviews');
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('vendor_services');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_listings');
        Schema::dropIfExists('service_categories');
    }
};
