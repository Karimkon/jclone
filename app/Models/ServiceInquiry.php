<?php

// ==========================================
// FILE: app/Models/ServiceInquiry.php
// ==========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceInquiry extends Model
{
    protected $fillable = [
        'vendor_service_id', 'vendor_profile_id', 'user_id',
        'name', 'phone', 'email', 'message', 'status'
    ];

    public function service() { return $this->belongsTo(VendorService::class, 'vendor_service_id'); }
    public function vendor() { return $this->belongsTo(VendorProfile::class, 'vendor_profile_id'); }
    public function user() { return $this->belongsTo(User::class); }

    public function scopeNew($q) { return $q->where('status', 'new'); }
}
