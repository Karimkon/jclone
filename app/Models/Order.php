<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'buyer_id',
        'vendor_profile_id',
        'status',
        'subtotal',
        'shipping',
        'taxes',
        'platform_commission',
        'total',
        'meta',
        // Add these new fields
        'confirmed_at',
        'processing_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'delivery_time_days',
        'processing_time_hours',
        'delivery_score',
    ];

    protected $casts = [
        'meta' => 'array',
        'confirmed_at' => 'datetime',
        'processing_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'delivery_score' => 'decimal:2',
    ];

    protected $appends = ['shipping_address'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function escrow()
    {
        return $this->hasOne(Escrow::class);
    }

    public function getShippingAddressAttribute()
    {
        $meta = $this->meta ?? [];
        
        if (isset($meta['shipping_address']) && is_array($meta['shipping_address'])) {
            return $meta['shipping_address'];
        }
        
        // Fallback so the Flutter app doesn't crash on null
        return [
            'recipient_name' => 'N/A',
            'recipient_phone' => 'N/A',
            'address_line_1' => 'Address not available',
            'city' => 'N/A',
            'full_address' => 'Address not available'
        ];
    }

    // Add this method to check if shipping address exists
    public function hasShippingAddress()
    {
        $meta = $this->meta ?? [];
        return isset($meta['shipping_address']) && is_array($meta['shipping_address']);
    }

    /**
     * Check if order can be marked as delivered
     */
    public function canBeDelivered()
    {
        return in_array($this->status, ['processing', 'shipped']) && !$this->delivered_at;
    }

    /**
     * Check if order can be shipped
     */
    public function canBeShipped()
    {
        return in_array($this->status, ['paid', 'processing']) && !$this->shipped_at;
    }

    /**
     * Check if order can be marked as processing
     */
    public function canBeProcessing()
    {
        return in_array($this->status, ['paid', 'confirmed']) && !$this->processing_at;
    }

    /**
     * Calculate delivery time when order is marked as delivered
     */
    public function calculateDeliveryTime()
    {
        if ($this->shipped_at && $this->delivered_at) {
            $this->delivery_time_days = $this->shipped_at->diffInDays($this->delivered_at);
            return $this->delivery_time_days;
        }
        return null;
    }

    /**
     * Calculate processing time when order is shipped
     */
    public function calculateProcessingTime()
    {
        if ($this->processing_at && $this->shipped_at) {
            $this->processing_time_hours = $this->processing_at->diffInHours($this->shipped_at);
            return $this->processing_time_hours;
        }
        return null;
    }

    /**
     * Calculate delivery score based on delivery time
     */
    public function calculateDeliveryScore()
    {
        if (!$this->delivery_time_days) {
            return 50; // Default score if no delivery time
        }

        // Simple scoring: faster = higher score
        $deliveryTime = $this->delivery_time_days;
        
        if ($deliveryTime <= 1) return 100;
        if ($deliveryTime <= 2) return 95;
        if ($deliveryTime <= 3) return 90;
        if ($deliveryTime <= 5) return 80;
        if ($deliveryTime <= 7) return 70;
        if ($deliveryTime <= 10) return 60;
        if ($deliveryTime <= 14) return 50;
        if ($deliveryTime <= 21) return 40;
        if ($deliveryTime <= 30) return 30;
        return 20;
    }

    /**
     * Update order status with timestamp tracking
     */
    public function updateStatusWithTimestamps($status)
    {
        $oldStatus = $this->status;
        $updates = ['status' => $status];
        $now = now();

        // Set timestamps based on status change
        switch ($status) {
            case 'processing':
                if (!$this->processing_at) {
                    $updates['processing_at'] = $now;
                }
                break;
                
            case 'shipped':
                if (!$this->shipped_at) {
                    $updates['shipped_at'] = $now;
                    
                    // Calculate processing time
                    if ($this->processing_at) {
                        $updates['processing_time_hours'] = $this->processing_at->diffInHours($now);
                    }
                }
                break;
                
            case 'delivered':
                if (!$this->delivered_at) {
                    $updates['delivered_at'] = $now;
                    
                    // Calculate delivery time
                    if ($this->shipped_at) {
                        $updates['delivery_time_days'] = $this->shipped_at->diffInDays($now);
                        
                        // Calculate delivery score
                        if (isset($updates['delivery_time_days'])) {
                            $updates['delivery_score'] = $this->calculateDeliveryScore($updates['delivery_time_days']);
                        }
                    }
                }
                break;
                
            case 'cancelled':
                if (!$this->cancelled_at) {
                    $updates['cancelled_at'] = $now;
                }
                break;
        }

        // Update the order
        $this->update($updates);
        
        // Update vendor performance after delivery
        if ($status === 'delivered' && $oldStatus !== 'delivered') {
            $this->updateVendorPerformance();
        }

        return $this;
    }

    /**
     * Update vendor performance metrics after delivery
     */
    private function updateVendorPerformance()
    {
        if ($this->vendor_profile_id && $this->delivered_at) {
            // Use the VendorPerformance model's calculateForVendor method
            $performanceData = VendorPerformance::calculateForVendor($this->vendor_profile_id);
            
            VendorPerformance::updateOrCreate(
                ['vendor_profile_id' => $this->vendor_profile_id],
                array_merge($performanceData, ['last_calculated_at' => now()])
            );
        }
    }

    /**
     * Get delivery status timeline
     */
    public function getDeliveryTimeline()
    {
        return [
            'ordered' => [
                'date' => $this->created_at,
                'completed' => true,
            ],
            'confirmed' => [
                'date' => $this->confirmed_at,
                'completed' => (bool) $this->confirmed_at,
            ],
            'processing' => [
                'date' => $this->processing_at,
                'completed' => (bool) $this->processing_at,
            ],
            'shipped' => [
                'date' => $this->shipped_at,
                'completed' => (bool) $this->shipped_at,
            ],
            'delivered' => [
                'date' => $this->delivered_at,
                'completed' => (bool) $this->delivered_at,
            ],
        ];
    }

    /**
     * Get estimated delivery date (if shipped)
     */
    public function getEstimatedDeliveryDate()
    {
        if ($this->shipped_at) {
            // Add average delivery time for this vendor
            $vendorPerformance = VendorPerformance::where('vendor_profile_id', $this->vendor_profile_id)->first();
            $avgDeliveryTime = $vendorPerformance ? $vendorPerformance->avg_delivery_time_days : 7;
            
            return $this->shipped_at->addDays(ceil($avgDeliveryTime));
        }
        
        return null;
    }

    /**
     * Check if delivery is on time
     */
    public function isDeliveryOnTime()
    {
        if (!$this->delivered_at || !$this->shipped_at) {
            return null;
        }
        
        $deliveryTime = $this->delivery_time_days;
        return $deliveryTime <= 7; // On time if delivered within 7 days of shipping
    }

    // Status:
public function canBeMarkedAsDelivered()
{
    $meta = $this->meta ?? [];
    $paymentMethod = $meta['payment_method'] ?? null;
    
    // For COD orders, only buyer should mark as delivered
    if ($paymentMethod === 'cash_on_delivery') {
        return $this->status === 'shipped' && !$this->delivered_at;
    }
    
    // For prepaid orders, vendor can mark as delivered
    return in_array($this->status, ['shipped', 'processing']) && !$this->delivered_at;
}

    /**
     * Get delivery performance badge
     */
    public function getDeliveryPerformanceBadge()
    {
        if (!$this->delivery_score) {
            return null;
        }
        
        $score = $this->delivery_score;
        
        if ($score >= 90) {
            return [
                'color' => 'green',
                'text' => 'Excellent Delivery',
                'icon' => 'fa-rocket',
            ];
        } elseif ($score >= 80) {
            return [
                'color' => 'blue',
                'text' => 'Fast Delivery',
                'icon' => 'fa-bolt',
            ];
        } elseif ($score >= 70) {
            return [
                'color' => 'yellow',
                'text' => 'Good Delivery',
                'icon' => 'fa-check-circle',
            ];
        } elseif ($score >= 60) {
            return [
                'color' => 'orange',
                'text' => 'Average Delivery',
                'icon' => 'fa-clock',
            ];
        } else {
            return [
                'color' => 'red',
                'text' => 'Slow Delivery',
                'icon' => 'fa-exclamation-triangle',
            ];
        }
    }
}