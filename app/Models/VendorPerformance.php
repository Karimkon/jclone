<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPerformance extends Model
{
    protected $fillable = [
        'vendor_profile_id',
        'total_orders',
        'delivered_orders',
        'avg_delivery_time_days',
        'on_time_delivery_rate',
        'delivery_score',
        'last_calculated_at',
    ];

    protected $casts = [
        'last_calculated_at' => 'datetime',
        'avg_delivery_time_days' => 'decimal:2',
        'on_time_delivery_rate' => 'decimal:2',
        'delivery_score' => 'decimal:2',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    /**
     * Calculate performance for a vendor
     */
    public static function calculateForVendor($vendorId)
    {
        // Get all delivered orders for this vendor
        $deliveredOrders = Order::where('vendor_profile_id', $vendorId)
            ->where('status', 'delivered')
            ->whereNotNull('delivered_at')
            ->get();
        
        $totalOrders = Order::where('vendor_profile_id', $vendorId)->count();
        
        if ($deliveredOrders->isEmpty()) {
            // Default values for new vendors
            return [
                'total_orders' => $totalOrders,
                'delivered_orders' => 0,
                'avg_delivery_time_days' => 0,
                'on_time_delivery_rate' => 0,
                'delivery_score' => 50, // Default score
            ];
        }
        
        // Calculate average delivery time
        $avgDeliveryTime = $deliveredOrders->avg('delivery_time_days') ?? 0;
        
        // Calculate on-time delivery rate (within 7 days)
        $onTimeDeliveries = $deliveredOrders->where('delivery_time_days', '<=', 7)->count();
        $onTimeRate = ($onTimeDeliveries / $deliveredOrders->count()) * 100;
        
        // Simple delivery score calculation
        $score = 100;
        
        // Penalty for slow delivery
        if ($avgDeliveryTime > 7) {
            $score -= 30;
        } elseif ($avgDeliveryTime > 3) {
            $score -= 15;
        }
        
        // Bonus for good on-time rate
        if ($onTimeRate >= 90) {
            $score += 20;
        } elseif ($onTimeRate >= 80) {
            $score += 10;
        }
        
        $score = max(0, min(100, $score));
        
        return [
            'total_orders' => $totalOrders,
            'delivered_orders' => $deliveredOrders->count(),
            'avg_delivery_time_days' => round($avgDeliveryTime, 1),
            'on_time_delivery_rate' => round($onTimeRate, 1),
            'delivery_score' => round($score, 1),
        ];
    }

    /**
 * Update or create performance record for a vendor
 */
public static function updateForVendor($vendorId)
{
    $performanceData = self::calculateForVendor($vendorId);
    
    return self::updateOrCreate(
        ['vendor_profile_id' => $vendorId],
        array_merge($performanceData, ['last_calculated_at' => now()])
    );
}
}