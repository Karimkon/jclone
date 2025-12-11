<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'type',
        'attachment_path',
        'attachment_name',
        'read_at',
        'is_deleted',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'is_deleted' => 'boolean',
    ];

    /**
     * Get the conversation this message belongs to
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the sender (user) of this message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Check if message is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if message was sent by specific user
     */
    public function isSentBy(int $userId): bool
    {
        return $this->sender_id === $userId;
    }

    /**
     * Get attachment URL (if any)
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if ($this->attachment_path) {
            return asset('storage/' . $this->attachment_path);
        }
        return null;
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for non-deleted messages
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Get formatted timestamp
     */
    public function getFormattedTimeAttribute(): string
    {
        $now = now();
        $diff = $this->created_at->diffInDays($now);

        if ($diff === 0) {
            return $this->created_at->format('g:i A'); // Today: 2:30 PM
        } elseif ($diff === 1) {
            return 'Yesterday ' . $this->created_at->format('g:i A');
        } elseif ($diff < 7) {
            return $this->created_at->format('l g:i A'); // Day name: Monday 2:30 PM
        } else {
            return $this->created_at->format('M j, Y g:i A'); // Dec 15, 2024 2:30 PM
        }
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Update conversation's last_message_at when a message is created
        static::created(function ($message) {
            $message->conversation->update([
                'last_message_at' => $message->created_at,
            ]);
        });
    }
}
