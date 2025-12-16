<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'image',
        'is_active', 'parent_id', 'order', 'meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    // Appends for automatic inclusion
    protected $appends = ['total_listings_count'];

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all descendant category IDs (children, grandchildren, etc.)
     */
    public function getDescendantIds()
    {
        $ids = [$this->id];
        
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getDescendantIds());
        }
        
        return $ids;
    }

    /**
     * Get total listings count including all descendants
     */
    public function getTotalListingsCountAttribute()
    {
        $categoryIds = $this->getDescendantIds();
        
        return Listing::whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->count();
    }

    /**
     * Get listings count for this category only (direct listings)
     */
    public function getDirectListingsCountAttribute()
    {
        return $this->listings()
            ->where('is_active', true)
            ->count();
    }
}