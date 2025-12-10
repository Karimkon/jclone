<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorReviewController extends Controller
{
    /**
     * Display vendor's product reviews
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        
        if (!$vendor) {
            abort(403, 'Vendor profile not found');
        }

        $filter = $request->get('filter', 'all'); // all, pending_response, responded

        $query = Review::where('vendor_profile_id', $vendor->id)
            ->with(['user:id,name', 'listing:id,title', 'order:id,order_number'])
            ->orderBy('created_at', 'desc');

        if ($filter === 'pending_response') {
            $query->whereNull('vendor_response');
        } elseif ($filter === 'responded') {
            $query->whereNotNull('vendor_response');
        }

        $reviews = $query->paginate(15);

        // Get stats
        $stats = [
            'total_reviews' => Review::where('vendor_profile_id', $vendor->id)->count(),
            'average_rating' => Review::getVendorAverageRating($vendor->id),
            'pending_response' => Review::where('vendor_profile_id', $vendor->id)
                ->whereNull('vendor_response')
                ->count(),
            'distribution' => $this->getVendorRatingDistribution($vendor->id),
        ];

        return view('vendor.reviews.index', compact('reviews', 'stats', 'filter'));
    }

    /**
     * Show a specific review
     */
    public function show($id)
    {
        $vendor = Auth::user()->vendorProfile;
        
        $review = Review::where('vendor_profile_id', $vendor->id)
            ->with(['user:id,name', 'listing.images', 'order', 'orderItem'])
            ->findOrFail($id);

        return view('vendor.reviews.show', compact('review'));
    }

    /**
     * Respond to a review
     */
    public function respond(Request $request, $id)
    {
        $vendor = Auth::user()->vendorProfile;
        
        $review = Review::where('vendor_profile_id', $vendor->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'vendor_response' => 'required|string|max:1000',
        ]);

        $review->update([
            'vendor_response' => $validated['vendor_response'],
            'vendor_responded_at' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Response submitted successfully',
            ]);
        }

        return redirect()->back()->with('success', 'Response submitted successfully');
    }

    /**
     * Update vendor response
     */
    public function updateResponse(Request $request, $id)
    {
        $vendor = Auth::user()->vendorProfile;
        
        $review = Review::where('vendor_profile_id', $vendor->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'vendor_response' => 'required|string|max:1000',
        ]);

        $review->update([
            'vendor_response' => $validated['vendor_response'],
        ]);

        return redirect()->back()->with('success', 'Response updated successfully');
    }

    /**
     * Delete vendor response
     */
    public function deleteResponse($id)
    {
        $vendor = Auth::user()->vendorProfile;
        
        $review = Review::where('vendor_profile_id', $vendor->id)
            ->findOrFail($id);

        $review->update([
            'vendor_response' => null,
            'vendor_responded_at' => null,
        ]);

        return redirect()->back()->with('success', 'Response deleted');
    }

    /**
     * Get vendor's rating distribution
     */
    private function getVendorRatingDistribution($vendorProfileId)
    {
        $reviews = Review::where('vendor_profile_id', $vendorProfileId)
            ->where('status', 'approved')
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[$i] = $reviews[$i] ?? 0;
        }

        return $distribution;
    }
}