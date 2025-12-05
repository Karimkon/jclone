<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClearingAgent extends Model
{
    protected $fillable = [
        'user_id','company_name','license_number','phone','email','meta','is_active'
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
    ];
}
