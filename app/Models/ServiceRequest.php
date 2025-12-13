<?php


// ==========================================
// FILE: app/Models/ServiceRequest.php
// ==========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'vendor_service_id', 'vendor_profile_id', 'user_id', 'request_number',
        'customer_name', 'customer_phone', 'customer_email', 'description',
        'location', 'address', 'preferred_date', 'preferred_time', 'urgency',
        'images', 'budget_min', 'budget_max', 'quoted_price', 'final_price',
        'status', 'accepted_at', 'completed_at', 'vendor_notes', 'meta'
    ];

    protected $casts = [
        'images' => 'array',
        'meta' => 'array',
        'preferred_date' => 'date',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($req) {
            if (empty($req->request_number)) {
                $req->request_number = 'SR-' . strtoupper(uniqid());
            }
        });
    }

    // Relationships
    public function service() { return $this->belongsTo(VendorService::class, 'vendor_service_id'); }
    public function vendor() { return $this->belongsTo(VendorProfile::class, 'vendor_profile_id'); }
    public function user() { return $this->belongsTo(User::class); }
    public function review() { return $this->hasOne(ServiceReview::class); }

    // Scopes
    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeActive($q) { return $q->whereIn('status', ['pending', 'quoted', 'accepted', 'in_progress']); }
    public function scopeCompleted($q) { return $q->where('status', 'completed'); }

    // Helpers
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Awaiting Response', 'quoted' => 'Quote Received',
            'accepted' => 'Accepted', 'in_progress' => 'In Progress',
            'completed' => 'Completed', 'cancelled' => 'Cancelled',
            default => ucfirst($this->status)
        };
    }

    public function canBeQuoted() { return $this->status === 'pending'; }
    public function canBeAccepted() { return $this->status === 'quoted'; }
    public function canBeCompleted() { return $this->status === 'in_progress'; }
}
