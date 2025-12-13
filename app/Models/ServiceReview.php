<?php

// ==========================================
// FILE: app/Models/ServiceReview.php
// ==========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReview extends Model
{
    protected $fillable = [
        'vendor_service_id', 'service_request_id', 'user_id', 'vendor_profile_id',
        'rating', 'comment', 'images', 'vendor_response', 'responded_at',
        'is_verified', 'is_visible'
    ];

    protected $casts = [
        'images' => 'array',
        'is_verified' => 'boolean',
        'is_visible' => 'boolean',
        'responded_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::saved(function ($review) {
            $review->service->updateRating();
        });
    }

    public function service() { return $this->belongsTo(VendorService::class, 'vendor_service_id'); }
    public function request() { return $this->belongsTo(ServiceRequest::class, 'service_request_id'); }
    public function user() { return $this->belongsTo(User::class); }
    public function vendor() { return $this->belongsTo(VendorProfile::class, 'vendor_profile_id'); }

    public function scopeVisible($q) { return $q->where('is_visible', true); }
}
