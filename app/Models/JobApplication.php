<?php

// ==========================================
// FILE: app/Models/JobApplication.php
// ==========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job_listing_id', 'user_id', 'applicant_name', 'applicant_email',
        'applicant_phone', 'cover_letter', 'cv_path', 'expected_salary',
        'status', 'notes', 'reviewed_at', 'meta'
    ];

    protected $casts = ['meta' => 'array', 'reviewed_at' => 'datetime'];

    public function job() { return $this->belongsTo(JobListing::class, 'job_listing_id'); }
    public function user() { return $this->belongsTo(User::class); }

    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeShortlisted($q) { return $q->where('status', 'shortlisted'); }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending Review', 'reviewed' => 'Reviewed',
            'shortlisted' => 'Shortlisted', 'interviewed' => 'Interviewed',
            'offered' => 'Offer Made', 'hired' => 'Hired', 'rejected' => 'Not Selected',
            default => ucfirst($this->status)
        };
    }

    public function getCvUrlAttribute()
    {
        return $this->cv_path ? asset('storage/' . $this->cv_path) : null;
    }
}