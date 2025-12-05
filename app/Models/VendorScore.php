<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorScore extends Model
{
    protected $fillable = [
        'vendor_profile_id','score','factors'
    ];

    protected $casts = [
        'factors' => 'array',
    ];
}

