<?php

// ==========================================
// FILE: app/Models/VendorService.php
// ==========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VendorService extends Model
{
    protected $fillable = [
        'vendor_profile_id', 'service_category_id', 'title', 'slug', 'description',
        'pricing_type', 'price', 'price_max', 'duration', 'location', 'city', 'is_mobile',
        'features', 'images', 'is_featured', 'is_active',
        'views_count', 'inquiries_count', 'bookings_count',
        'average_rating', 'reviews_count', 'meta'
    ];

    protected $casts = [
        'features' => 'array',
        'images' => 'array',
        'meta' => 'array',
        'is_mobile' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->title) . '-' . Str::random(4);
            }
        });
    }

    // Relationships
    public function vendor() { return $this->belongsTo(VendorProfile::class, 'vendor_profile_id'); }
    public function category() { return $this->belongsTo(ServiceCategory::class, 'service_category_id'); }
    public function requests() { return $this->hasMany(ServiceRequest::class, 'vendor_service_id'); }
    public function reviews() { return $this->hasMany(ServiceReview::class, 'vendor_service_id'); }
    public function inquiries() { return $this->hasMany(ServiceInquiry::class, 'vendor_service_id'); }

    // Scopes
    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeFeatured($q) { return $q->where('is_featured', true); }

    // Helpers
    public function getFormattedPriceAttribute()
    {
        if ($this->pricing_type === 'negotiable') return 'Negotiable';
        if ($this->pricing_type === 'free_quote') return 'Get Free Quote';
        if ($this->pricing_type === 'starting_from') return 'From UGX ' . number_format($this->price);
        if ($this->price_max && $this->price_max > $this->price) {
            return 'UGX ' . number_format($this->price) . ' - ' . number_format($this->price_max);
        }
        if ($this->price) {
            $suffix = $this->pricing_type === 'hourly' ? '/hr' : '';
            return 'UGX ' . number_format($this->price) . $suffix;
        }
        return 'Contact for price';
    }

    public function getPrimaryImageAttribute()
    {
        return $this->images && count($this->images) > 0 ? asset('storage/' . $this->images[0]) : null;
    }

    public function incrementViews() { $this->increment('views_count'); }
    
    public function updateRating()
    {
        $this->average_rating = $this->reviews()->avg('rating') ?? 0;
        $this->reviews_count = $this->reviews()->count();
        $this->save();
    }
}