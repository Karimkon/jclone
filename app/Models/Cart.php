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

    // addItem method:
public function addItem($listing, $quantity = 1, $variantId = null, $color = null, $size = null)
{
    // Get items array
    $items = $this->items ?? [];
    
    if ($variantId) {
        $variant = \App\Models\ListingVariant::find($variantId);
        if (!$variant || $variant->stock < $quantity) {
            throw new \Exception('Variant not available or insufficient stock');
        }
        $price = $variant->display_price;
        $stock = $variant->stock;
        
        // Use variant attributes if color/size not provided
        if (empty($color) && isset($variant->attributes['color'])) {
            $color = $variant->attributes['color'];
        }
        if (empty($size) && isset($variant->attributes['size'])) {
            $size = $variant->attributes['size'];
        }
    } else {
        if ($listing->stock < $quantity) {
            throw new \Exception('Insufficient stock');
        }
        $price = $listing->price;
        $stock = $listing->stock;
    }
    
    // Create a unique key for the item
    $itemKey = $this->generateItemKey($listing->id, $variantId, $color, $size);
    
    // Check if same item already exists
    $existingKey = $this->findItemKey($items, $listing->id, $variantId, $color, $size);
    
    if ($existingKey !== null) {
        // Update existing item
        $items[$existingKey]['quantity'] += $quantity;
        $items[$existingKey]['total'] = $items[$existingKey]['quantity'] * $items[$existingKey]['unit_price'];
        
        // Update stock if needed
        if ($variantId && $variant) {
            $items[$existingKey]['stock'] = $variant->stock;
        } else {
            $items[$existingKey]['stock'] = $listing->stock;
        }
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
            'stock' => $stock,
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
        
        $items[$itemKey] = $newItem;
    }
    
    // FIXED: Keep as associative array, don't reindex!
    $this->items = $items;
    $this->recalculateTotals();
    $this->save();
    
    return $this;
}


private function generateItemKey($listingId, $variantId, $color, $size)
{
    // Create a unique key based on all identifying attributes
    $parts = [
        $listingId,
        $variantId ?? 'base',
        $color ?? 'nocolor',
        $size ?? 'nosize'
    ];
    
    return implode('_', $parts);
}
    /**
     * Find item key in items array
     */
private function findItemKey($items, $listingId, $variantId, $color, $size)
{
    $targetKey = $this->generateItemKey($listingId, $variantId, $color, $size);
    
    // Direct lookup since we're using keys
    return isset($items[$targetKey]) ? $targetKey : null;
}

   public function removeItem($listingId)
{
    $items = $this->items ?? [];
    
    // Remove all items with this listing_id
    foreach ($items as $key => $item) {
        if ($item['listing_id'] == $listingId) {
            unset($items[$key]);
        }
    }
    
    $this->items = $items; // Keep as associative array
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
    
    $targetKey = $this->generateItemKey($listingId, $variantId, $color, $size);
    
    if (isset($items[$targetKey])) {
        unset($items[$targetKey]);
        $this->items = $items; // Keep as associative array
        $this->recalculateTotals();
        $this->save();
        return $this;
    }
    
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
    
    foreach ($items as $itemKey => $item) {
        $listing = \App\Models\Listing::with('images', 'vendor')->find($item['listing_id']);
        
        if ($listing) {
            $enrichedItem = $item;
            $enrichedItem['item_key'] = $itemKey; // Add the key for reference
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
                        'display_price' => $variant->display_price,
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