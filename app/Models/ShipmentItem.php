<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentItem extends Model
{
    protected $fillable = [
        'shipment_id','warehouse_stock_id','quantity'
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
