<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'recipient_phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state_region',
        'postal_code',
        'country',
        'delivery_instructions',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set as default address (and unset others)
     */
    public function setAsDefault()
    {
        // Remove default from all other addresses for this user
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        // Set this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Get formatted address
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state_region,
            $this->postal_code,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Boot method to ensure only one default per user
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($address) {
            if ($address->is_default) {
                // Unset other defaults for this user
                static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
        });

        static::deleting(function ($address) {
            // If deleting default address, set another as default
            if ($address->is_default) {
                $nextAddress = static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->first();
                
                if ($nextAddress) {
                    $nextAddress->update(['is_default' => true]);
                }
            }
        });
    }
}