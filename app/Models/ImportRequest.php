<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportRequest extends Model
{
    protected $fillable = [
        'listing_id','vendor_profile_id','supplier_price','freight','insurance',
        'calc_method','weight_kg','tariff_meta','status'
    ];

    protected $casts = [
        'tariff_meta' => 'array',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function costs()
    {
        return $this->hasOne(ImportCost::class);
    }
}
