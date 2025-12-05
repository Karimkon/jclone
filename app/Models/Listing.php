<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model {
    use HasFactory;

    protected $fillable = [
        'vendor_profile_id','title','description','sku','price','weight_kg','origin','condition','category','stock','attributes','is_active'
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function vendor() {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    public function images() {
        return $this->hasMany(ListingImage::class);
    }

    public function scopeImported($query) {
        return $query->where('origin', 'imported');
    }
}
