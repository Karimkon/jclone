<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'items', 'subtotal', 
        'shipping', 'tax', 'total', 'meta'
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'shipping' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getItemCountAttribute()
    {
        return count($this->items ?? []);
    }

    /**
     * Add item to cart with variant support
     */
    public function addItem($listing, $quantity = 1, $variantId = null, $color = null, $size = null)
    {
        // Get items array (always work with a copy)
        $items = $this->items ?? [];
        
        // Check if variant is specified
        $variant = null;
        if ($variantId) {
            $variant = \App\Models\ListingVariant::find($variantId);
            $price = $variant->display_price;
        } else {
            $price = $listing->price;
        }
        
        // Check if same item already exists
        $itemKey = $this->findItemKey($items, $listing->id, $variantId, $color, $size);
        
        if ($itemKey !== null) {
            // Update existing item
            $items[$itemKey]['quantity'] += $quantity;
            $items[$itemKey]['total'] = $items[$itemKey]['quantity'] * $items[$itemKey]['unit_price'];
        } else {
            // Add new item
            $newItem = [
                'listing_id' => $listing->id,
                'title' => $listing->title,
                'image' => $listing->images->first() ? asset('storage/' . $listing->images->first()->path) : null,
                'vendor_name' => $listing->vendor->business_name ?? 'Vendor',
                'unit_price' => $price,
                'quantity' => $quantity,
                'total' => $price * $quantity,
                'weight_kg' => $listing->weight_kg,
                'origin' => $listing->origin,
                'added_at' => now()->toDateTimeString(),
            ];
            
            // Add variant info if provided
            if ($variantId) {
                $newItem['variant_id'] = $variantId;
            }
            if ($color) {
                $newItem['color'] = $color;
            }
            if ($size) {
                $newItem['size'] = $size;
            }
            
            $items[] = $newItem;
        }
        
        // Assign back to model
        $this->items = $items;
        $this->recalculateTotals();
        $this->save();
        
        return $this;
    }

    /**
     * Find item key in items array
     */
    private function findItemKey($items, $listingId, $variantId, $color, $size)
    {
        foreach ($items as $key => $item) {
            // Check if listing ID matches
            if ($item['listing_id'] != $listingId) {
                continue;
            }
            
            // Safely get variant_id with null coalescing
            $itemVariantId = $item['variant_id'] ?? null;
            $itemColor = $item['color'] ?? null;
            $itemSize = $item['size'] ?? null;
            
            // Compare variant ID (both null or same value)
            $variantMatch = ($itemVariantId === $variantId);
            
            // Compare color (both null or same value)
            $colorMatch = ($itemColor === $color);
            
            // Compare size (both null or same value)
            $sizeMatch = ($itemSize === $size);
            
            // All must match
            if ($variantMatch && $colorMatch && $sizeMatch) {
                return $key;
            }
        }
        
        return null;
    }

    public function removeItem($listingId)
    {
        $items = $this->items ?? [];
        $items = array_filter($items, function($item) use ($listingId) {
            return $item['listing_id'] != $listingId;
        });
        
        $this->items = array_values($items);
        $this->recalculateTotals();
        $this->save();
        
        return $this;
    }

    /**
     * Remove specific variant item
     */
    public function removeVariantItem($listingId, $variantId = null, $color = null, $size = null)
    {
        $items = $this->items ?? [];
        
        $items = array_filter($items, function($item) use ($listingId, $variantId, $color, $size) {
            // Check if listing ID matches
            if ($item['listing_id'] != $listingId) {
                return true; // Keep this item
            }
            
            // Safely get values with null coalescing
            $itemVariantId = $item['variant_id'] ?? null;
            $itemColor = $item['color'] ?? null;
            $itemSize = $item['size'] ?? null;
            
            // Check if all attributes match
            return !($itemVariantId === $variantId && 
                    $itemColor === $color && 
                    $itemSize === $size);
        });
        
        $this->items = array_values($items);
        $this->recalculateTotals();
        $this->save();
        
        return $this;
    }

    public function updateQuantity($listingId, $quantity, $variantId = null, $color = null, $size = null)
    {
        $items = $this->items ?? [];
        $itemKey = $this->findItemKey($items, $listingId, $variantId, $color, $size);
        
        if ($itemKey !== null && $quantity > 0) {
            $items[$itemKey]['quantity'] = $quantity;
            $items[$itemKey]['total'] = $items[$itemKey]['quantity'] * $items[$itemKey]['unit_price'];
            
            $this->items = $items;
            $this->recalculateTotals();
            $this->save();
        } elseif ($itemKey !== null && $quantity <= 0) {
            // Remove item if quantity is 0 or negative
            $this->removeVariantItem($listingId, $variantId, $color, $size);
        }
        
        return $this;
    }

    public function recalculateTotals()
    {
        $items = $this->items ?? [];
        $subtotal = 0;
        $totalWeight = 0;
        
        foreach ($items as $item) {
            $subtotal += $item['total'] ?? 0;
            $totalWeight += ($item['weight_kg'] ?? 0) * ($item['quantity'] ?? 0);
        }
        
        // Calculate shipping (simplified for MVP)
        $shipping = $this->calculateShipping($totalWeight);
        
        // Calculate tax (simplified for MVP)
        $tax = $subtotal * 0.18; // 18% VAT
        
        $this->subtotal = $subtotal;
        $this->shipping = $shipping;
        $this->tax = $tax;
        $this->total = $subtotal + $shipping + $tax;
        
        return $this;
    }

    private function calculateShipping($weight)
    {
        // Simplified shipping calculation
        if ($weight <= 1) return 5;
        if ($weight <= 5) return 10;
        if ($weight <= 10) return 15;
        if ($weight <= 20) return 25;
        return 50;
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        $this->items = [];
        $this->subtotal = 0;
        $this->shipping = 0;
        $this->tax = 0;
        $this->total = 0;
        $this->save();
        
        return $this;
    }

    /**
     * Get cart summary for frontend
     */
    public function getSummary()
    {
        return [
            'item_count' => $this->item_count,
            'subtotal' => $this->subtotal,
            'shipping' => $this->shipping,
            'tax' => $this->tax,
            'total' => $this->total,
            'items' => $this->items ?? []
        ];
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Get enriched cart items with listing data
     */
    public function getEnrichedItems()
    {
        $items = $this->items ?? [];
        $enrichedItems = [];
        
        foreach ($items as $item) {
            $listing = \App\Models\Listing::with('images', 'vendor')->find($item['listing_id']);
            
            if ($listing) {
                $enrichedItem = $item;
                $enrichedItem['listing'] = [
                    'id' => $listing->id,
                    'title' => $listing->title,
                    'slug' => $listing->slug ?? null,
                    'images' => $listing->images,
                    'vendor' => $listing->vendor,
                    'stock' => $listing->stock,
                    'is_active' => $listing->is_active,
                ];
                
                // Add variant data if exists
                if (isset($item['variant_id']) && $item['variant_id']) {
                    $variant = \App\Models\ListingVariant::find($item['variant_id']);
                    if ($variant) {
                        $enrichedItem['variant'] = [
                            'id' => $variant->id,
                            'sku' => $variant->sku,
                            'price' => $variant->price,
                            'sale_price' => $variant->sale_price,
                            'stock' => $variant->stock,
                            'attributes' => $variant->attributes,
                        ];
                    }
                }
                
                $enrichedItems[] = $enrichedItem;
            }
        }
        
        return $enrichedItems;
    }
}