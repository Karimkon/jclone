<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'sku',
        'display_name',
        'price',
        'sale_price',
        'stock',
        'attributes',
        'image',
        'is_default',
        'is_active'
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Get color from attributes JSON
     * Using property name that doesn't conflict with Eloquent internals
     */
    public function color()
    {
        $attrs = $this->castAttribute('attributes', $this->getAttributes()['attributes'] ?? null);
        return is_array($attrs) ? ($attrs['color'] ?? null) : null;
    }

    /**
     * Get size from attributes JSON
     */
    public function size()
    {
        $attrs = $this->castAttribute('attributes', $this->getAttributes()['attributes'] ?? null);
        return is_array($attrs) ? ($attrs['size'] ?? null) : null;
    }

    /**
     * Get storage from attributes JSON
     */
    public function storage()
    {
        $attrs = $this->castAttribute('attributes', $this->getAttributes()['attributes'] ?? null);
        return is_array($attrs) ? ($attrs['storage'] ?? null) : null;
    }

    /**
     * Get price with sale price consideration
     */
    public function getDisplayPriceAttribute()
    {
        return $this->sale_price ?: $this->price;
    }

    /**
     * Check if variant is in stock
     */
    public function getInStockAttribute()
    {
        return $this->stock > 0;
    }
}