<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorDocument extends Model
{
    protected $fillable = [
        'vendor_profile_id',
        'type',
        'path',
        'mime',
        'ocr_data',
        'status',
    ];

    protected $casts = [
        'ocr_data' => 'array',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }
    
    /**
     * Check if document is verified
     */
    public function isVerified()
    {
        return $this->status === 'verified';
    }
    
    /**
     * Check if document is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
    
    /**
     * Get file size in human readable format
     */
    public function getFileSizeAttribute()
    {
        $size = $this->ocr_data['size'] ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return round($size / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
