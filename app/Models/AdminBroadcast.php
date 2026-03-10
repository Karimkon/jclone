<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminBroadcast extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image_url',
        'route',
        'audience',
        'user_id',
        'total_recipients',
        'sent_count',
        'status',
        'created_by',
        'sent_at',
    ];

    protected $casts = [
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
