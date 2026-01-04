<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use App\Models\Listing;
use App\Models\VendorScore; // Add this
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display vendor's public store page
     */
    public function showStore($vendor)
    {
        // Find vendor by ID or business name slug
        $vendor = VendorProfile::with(['user', 'reviews'])
            ->where('id', $vendor)
            ->orWhere('business_name', str_replace('-', ' ', $vendor))
            ->firstOrFail();
        
        // Check if vendor is approved
        if ($vendor->vetting_status !== 'approved') {
            abort(404);
        }
        
        // Get vendor's active listings
        $listings = Listing::where('vendor_profile_id', $vendor->id)
            ->where('is_active', true)
            ->with(['images', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);
        
        // Get vendor stats
        $vendorStats = [
            'rating' => $vendor->average_rating ?? 0,
            'reviews' => $vendor->total_reviews ?? 0,
            'positive' => $vendor->positive_rating_percentage ?? 98,
            'joined_date' => $vendor->created_at->format('M Y'),
            'total_products' => $listings->total(),
            'total_sales' => $vendor->total_sales ?? 0,
        ];
        
        // Get vendor score for delivery performance
        $vendorScore = VendorScore::where('vendor_profile_id', $vendor->id)
            ->latest()
            ->first();
        
        // Default delivery stats
        $deliveryStats = [
            'score' => 50,
            'avg_time' => 0,
            'on_time_rate' => 0,
            'delivered_orders' => 0,
            'rating' => 3, // Default 3 stars
        ];
        
        // Calculate delivery stats from score if available
        if ($vendorScore) {
            $factors = $vendorScore->factors ?? [];
            
            $deliveryStats = [
                'score' => $vendorScore->score ?? 50,
                'avg_time' => $factors['avg_delivery_time_days'] ?? 0,
                'on_time_rate' => $factors['on_time_delivery_rate'] ?? 0,
                'delivered_orders' => $factors['delivered_orders'] ?? 0,
                'rating' => $this->calculateDeliveryRating($vendorScore->score ?? 50),
            ];
        }
        
        return view('marketplace.vendor-store', compact(
            'vendor', 
            'listings', 
            'vendorStats', 
            'deliveryStats',
            'vendorScore'
        ));
    }
    
    /**
     * Calculate delivery rating from score (1-5 stars)
     */
    private function calculateDeliveryRating($score)
    {
        if ($score >= 90) return 5;
        if ($score >= 80) return 4;
        if ($score >= 70) return 3;
        if ($score >= 60) return 2;
        return 1;
    }
}