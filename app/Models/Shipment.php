<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'tracking_number','warehouse_id','order_id',
        'status','documents'
    ];

    protected $casts = [
        'documents' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }
}
