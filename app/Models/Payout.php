<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_profile_id',
        'amount',
        'status',
        'method',
        'reference',
        'notes',
        'completed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }
}