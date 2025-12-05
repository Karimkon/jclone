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

    public function addItem($listing, $quantity = 1)
    {
        $items = $this->items ?? [];
        
        // Check if item already exists
        $found = false;
        foreach ($items as &$item) {
            if ($item['listing_id'] == $listing->id) {
                $item['quantity'] += $quantity;
                $item['total'] = $item['quantity'] * $item['unit_price'];
                $found = true;
                break;
            }
        }
        
        // Add new item if not found
        if (!$found) {
            $items[] = [
                'listing_id' => $listing->id,
                'title' => $listing->title,
                'image' => $listing->images->first() ? asset('storage/' . $listing->images->first()->path) : null,
                'vendor_name' => $listing->vendor->business_name ?? 'Vendor',
                'unit_price' => $listing->price,
                'quantity' => $quantity,
                'total' => $listing->price * $quantity,
                'weight_kg' => $listing->weight_kg,
                'origin' => $listing->origin,
            ];
        }
        
        $this->items = $items;
        $this->recalculateTotals();
        $this->save();
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
    }

    public function updateQuantity($listingId, $quantity)
    {
        $items = $this->items ?? [];
        foreach ($items as &$item) {
            if ($item['listing_id'] == $listingId) {
                $item['quantity'] = $quantity;
                $item['total'] = $item['quantity'] * $item['unit_price'];
                break;
            }
        }
        
        $this->items = $items;
        $this->recalculateTotals();
        $this->save();
    }

    public function recalculateTotals()
    {
        $subtotal = 0;
        $totalWeight = 0;
        
        foreach ($this->items ?? [] as $item) {
            $subtotal += $item['total'];
            $totalWeight += ($item['weight_kg'] ?? 0) * $item['quantity'];
        }
        
        // Calculate shipping (simplified for MVP)
        $shipping = $this->calculateShipping($totalWeight);
        
        // Calculate tax (simplified for MVP)
        $tax = $subtotal * 0.18; // 18% VAT
        
        $this->subtotal = $subtotal;
        $this->shipping = $shipping;
        $this->tax = $tax;
        $this->total = $subtotal + $shipping + $tax;
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
}