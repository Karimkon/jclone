<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobListing extends Model
{
    protected $fillable = [
        'vendor_profile_id', 'service_category_id', 'title', 'slug', 'description',
        'job_type', 'experience_level', 'location', 'city', 'is_remote',
        'salary_min', 'salary_max', 'salary_period', 'salary_negotiable',
        'requirements', 'responsibilities', 'benefits',
        'contact_email', 'contact_phone', 'application_method',
        'deadline', 'vacancies', 'applications_count', 'views_count',
        'is_featured', 'is_urgent', 'status', 'meta'
    ];

    protected $casts = [
        'requirements' => 'array',
        'responsibilities' => 'array',
        'benefits' => 'array',
        'meta' => 'array',
        'is_remote' => 'boolean',
        'salary_negotiable' => 'boolean',
        'is_featured' => 'boolean',
        'is_urgent' => 'boolean',
        'deadline' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($job) {
            if (empty($job->slug)) {
                $job->slug = Str::slug($job->title) . '-' . Str::random(6);
            }
        });
    }

    // Relationships
    public function vendor() { return $this->belongsTo(VendorProfile::class, 'vendor_profile_id'); }
    public function category() { return $this->belongsTo(ServiceCategory::class, 'service_category_id'); }
    public function applications() { return $this->hasMany(JobApplication::class); }

    // Scopes
    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeFeatured($q) { return $q->where('is_featured', true); }
    public function scopeUrgent($q) { return $q->where('is_urgent', true); }
    public function scopeNotExpired($q) {
        return $q->where(function ($q) {
            $q->whereNull('deadline')->orWhere('deadline', '>=', now()->toDateString());
        });
    }

    // Helpers
    public function getFormattedSalaryAttribute()
    {
        if ($this->salary_negotiable && !$this->salary_min && !$this->salary_max) return 'Negotiable';
        $period = $this->salary_period == 'monthly' ? '/month' : '/' . $this->salary_period;
        if ($this->salary_min && $this->salary_max) {
            return 'UGX ' . number_format($this->salary_min) . ' - ' . number_format($this->salary_max) . $period;
        }
        if ($this->salary_min) return 'From UGX ' . number_format($this->salary_min) . $period;
        return 'Negotiable';
    }

    public function getJobTypeLabelAttribute()
    {
        return match($this->job_type) {
            'full_time' => 'Full Time', 'part_time' => 'Part Time',
            'contract' => 'Contract', 'freelance' => 'Freelance',
            'internship' => 'Internship', 'temporary' => 'Temporary',
            default => ucfirst($this->job_type)
        };
    }

    public function getIsExpiredAttribute() { return $this->deadline && $this->deadline->isPast(); }
    public function hasUserApplied($userId) { return $this->applications()->where('user_id', $userId)->exists(); }
    public function incrementViews() { $this->increment('views_count'); }
}