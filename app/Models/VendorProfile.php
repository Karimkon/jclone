<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'vendor_type',
        'business_name',
        'country',
        'city',
        'address',
        'annual_turnover',
        'preferred_currency',
        'vetting_status',
        'vetting_notes',
        'meta',
    ];

    protected $casts = [
        'annual_turnover' => 'float',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function scores()
    {
        return $this->hasMany(VendorScore::class);
    }
    
    /**
     * Get the latest vendor score
     */
    public function latestScore()
    {
        return $this->scores()->latest()->first();
    }
    
    /**
     * Check if vendor is approved
     */
    public function isApproved()
    {
        return $this->vetting_status === 'approved';
    }
    
    /**
     * Check if vendor is pending
     */
    public function isPending()
    {
        return $this->vetting_status === 'pending';
    }
    
    /**
     * Get guarantor information
     */
    public function getGuarantorAttribute()
    {
        return $this->meta['guarantor'] ?? null;
    }
}