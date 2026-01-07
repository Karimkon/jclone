<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'buyer_id',
        'vendor_profile_id',
        'listing_id',
        'subject',
        'last_message_at',
        'status',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the buyer (user) in this conversation
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the vendor profile in this conversation
     */
    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    /**
     * Get the vendor user through vendor profile
     */
    public function vendor()
    {
        return $this->vendorProfile->user ?? null;
    }

    /**
     * Get the listing this conversation is about (if any)
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    /**
     * Get all messages in this conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the latest message
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Get unread messages count for a specific user
     */
    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Mark all messages as read for a specific user
     */
    public function markAsReadFor(int $userId): void
    {
        $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get the other participant in the conversation
     */
    public function getOtherParticipant(int $currentUserId)
    {
        if ($this->buyer_id === $currentUserId) {
            return $this->vendorProfile->user;
        }
        return $this->buyer;
    }

    /**
     * Check if user is participant in this conversation
     */
    public function isParticipant(int $userId): bool
    {
        return $this->buyer_id === $userId || 
               ($this->vendorProfile && $this->vendorProfile->user_id === $userId);
    }

    /**
     * Scope for active conversations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for user's conversations (either as buyer or vendor)
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('buyer_id', $userId)
              ->orWhereHas('vendorProfile', function ($vq) use ($userId) {
                  $vq->where('user_id', $userId);
              });
        });
    }

    /**
     * Get or create a conversation between buyer and vendor
     */
    public static function findOrCreateBetween(int $buyerId, int $vendorProfileId, ?int $listingId = null): self
    {
        // Check if a conversation already exists between this buyer and vendor profile
        $conversation = self::where('buyer_id', $buyerId)
            ->where('vendor_profile_id', $vendorProfileId)
            ->first();

        if ($conversation) {
            // Optional: Update listing context if it was null before
            if ($listingId && !$conversation->listing_id) {
                $conversation->update(['listing_id' => $listingId]);
            }
            return $conversation;
        }

        return self::create([
            'buyer_id' => $buyerId,
            'vendor_profile_id' => $vendorProfileId,
            'listing_id' => $listingId,
            'status' => 'active',
        ]);
    }
}
