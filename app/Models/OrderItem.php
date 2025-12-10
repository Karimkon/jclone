<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id','listing_id','title','quantity',
        'unit_price','line_total','attributes'
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    /**
 * Get the review for this order item
 */
public function review()
{
    return $this->hasOne(\App\Models\Review::class);
}

/**
 * Check if this item has been reviewed by the buyer
 */
public function hasReview()
{
    return $this->review()->exists();
}

/**
 * Check if this item can be reviewed
 */
public function canBeReviewed()
{
    // Must be delivered and not already reviewed
    return $this->order->status === 'delivered' && !$this->hasReview();
}
}
