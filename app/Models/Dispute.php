<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $fillable = [
        'order_id','raised_by','reason','status','evidence'
    ];

    protected $casts = [
        'evidence' => 'array',
    ];
}
