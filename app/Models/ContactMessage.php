<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'contact_type',
        'status',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    protected $attributes = [
        'status' => 'new'
    ];
}