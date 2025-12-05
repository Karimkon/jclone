<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationQueue extends Model
{
    protected $fillable = [
        'user_id','type','title','message','meta','status','sent_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'sent_at' => 'datetime',
    ];
}
