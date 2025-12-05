<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number','buyer_id','vendor_profile_id','status',
        'subtotal','shipping','taxes','platform_commission','total','meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function escrow()
    {
        return $this->hasOne(Escrow::class);
    }
}
