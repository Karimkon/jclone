<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseStock extends Model
{
    protected $fillable = [
        'warehouse_id','listing_id','serial_number',
        'quantity','status'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
