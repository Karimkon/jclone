<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallbackRequest extends Model
{
    protected $fillable = [
        'listing_id',
        'buyer_id',
        'vendor_profile_id',
        'name',
        'phone',
        'message',
        'status',
        'contacted_at',
        'vendor_notes',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForVendor($query, int $vendorProfileId)
    {
        return $query->where('vendor_profile_id', $vendorProfileId);
    }

    public function markAsContacted(?string $notes = null): void
    {
        $this->update([
            'status' => 'contacted',
            'contacted_at' => now(),
            'vendor_notes' => $notes,
        ]);
    }

    public function markAsCompleted(?string $notes = null): void
    {
        $this->update([
            'status' => 'completed',
            'vendor_notes' => $notes,
        ]);
    }
}