<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewVote;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Display buyer's reviews
     */
    public function index()
    {
        $reviews = Review::where('user_id', Auth::id())
            ->with(['listing.images', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get items pending review
        $pendingReviews = OrderItem::whereHas('order', function ($query) {
            $query->where('buyer_id', Auth::id())
                ->where('status', 'delivered');
        })
        ->whereDoesntHave('review', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->with(['listing.images', 'order'])
        ->get();

        return view('buyer.reviews.index', compact('reviews', 'pendingReviews'));
    }

    /**
     * Show form to create a review
     */
    public function create(Request $request)
    {
        $orderItemId = $request->get('order_item_id');
        
        $orderItem = OrderItem::with(['listing.images', 'order.vendorProfile'])
            ->findOrFail($orderItemId);

        // Verify ownership and eligibility
        if ($orderItem->order->buyer_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if ($orderItem->order->status !== 'delivered') {
            return redirect()->back()
                ->with('error', 'You can only review delivered items');
        }

        // Check if already reviewed
        $existingReview = Review::where('user_id', Auth::id())
            ->where('order_item_id', $orderItemId)
            ->first();

        if ($existingReview) {
            return redirect()->route('buyer.reviews.edit', $existingReview->id)
                ->with('info', 'You have already reviewed this item. You can edit your review.');
        }

        return view('buyer.reviews.create', compact('orderItem'));
    }

    /**
     * Store a new review
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
            'quality_rating' => 'nullable|integer|min:1|max:5',
            'value_rating' => 'nullable|integer|min:1|max:5',
            'shipping_rating' => 'nullable|integer|min:1|max:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $orderItem = OrderItem::with(['listing', 'order'])->findOrFail($validated['order_item_id']);

        // Verify ownership and eligibility
        if ($orderItem->order->buyer_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if ($orderItem->order->status !== 'delivered') {
            return back()->with('error', 'You can only review delivered items');
        }

        // Check for existing review
        if (!Review::canUserReview(Auth::id(), $orderItem->id)) {
            return back()->with('error', 'You have already reviewed this item');
        }

        DB::beginTransaction();
        try {
            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('reviews', 'public');
                    $imagePaths[] = $path;
                }
            }

            // Create the review
            $review = Review::create([
                'user_id' => Auth::id(),
                'listing_id' => $orderItem->listing_id,
                'order_id' => $orderItem->order_id,
                'order_item_id' => $orderItem->id,
                'vendor_profile_id' => $orderItem->listing->vendor_profile_id,
                'rating' => $validated['rating'],
                'title' => $validated['title'] ?? null,
                'comment' => $validated['comment'] ?? null,
                'quality_rating' => $validated['quality_rating'] ?? null,
                'value_rating' => $validated['value_rating'] ?? null,
                'shipping_rating' => $validated['shipping_rating'] ?? null,
                'images' => !empty($imagePaths) ? $imagePaths : null,
                'is_verified_purchase' => true,
                'status' => 'approved', // Auto-approve verified purchases
                'meta' => [
                    'user_agent' => $request->userAgent(),
                    'ip_hash' => hash('sha256', $request->ip()),
                ],
            ]);

            // Update listing's average rating (optional: cache this)
            $this->updateListingRatingCache($orderItem->listing_id);

            DB::commit();

            return redirect()->route('buyer.reviews.index')
                ->with('success', 'Thank you for your review!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Review submission error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to submit review. Please try again.');
        }
    }

    /**
     * Show form to edit a review
     */
    public function edit($id)
    {
        $review = Review::where('user_id', Auth::id())
            ->with(['listing.images', 'orderItem', 'order'])
            ->findOrFail($id);

        return view('buyer.reviews.edit', compact('review'));
    }

    /**
     * Update a review
     */
    public function update(Request $request, $id)
    {
        $review = Review::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
            'quality_rating' => 'nullable|integer|min:1|max:5',
            'value_rating' => 'nullable|integer|min:1|max:5',
            'shipping_rating' => 'nullable|integer|min:1|max:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_images' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Handle image removals
            $currentImages = $review->images ?? [];
            if ($request->has('remove_images')) {
                foreach ($request->remove_images as $imageToRemove) {
                    Storage::disk('public')->delete($imageToRemove);
                    $currentImages = array_filter($currentImages, fn($img) => $img !== $imageToRemove);
                }
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('reviews', 'public');
                    $currentImages[] = $path;
                }
            }

            $review->update([
                'rating' => $validated['rating'],
                'title' => $validated['title'] ?? null,
                'comment' => $validated['comment'] ?? null,
                'quality_rating' => $validated['quality_rating'] ?? null,
                'value_rating' => $validated['value_rating'] ?? null,
                'shipping_rating' => $validated['shipping_rating'] ?? null,
                'images' => !empty($currentImages) ? array_values($currentImages) : null,
            ]);

            // Update listing's average rating
            $this->updateListingRatingCache($review->listing_id);

            DB::commit();

            return redirect()->route('buyer.reviews.index')
                ->with('success', 'Review updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Review update error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update review. Please try again.');
        }
    }

    /**
     * Delete a review
     */
    public function destroy($id)
    {
        $review = Review::where('user_id', Auth::id())->findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Delete associated images
            if ($review->images) {
                foreach ($review->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $listingId = $review->listing_id;
            $review->delete();

            // Update listing's average rating
            $this->updateListingRatingCache($listingId);

            DB::commit();

            return redirect()->route('buyer.reviews.index')
                ->with('success', 'Review deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete review');
        }
    }

    /**
     * Vote on a review (helpful/unhelpful)
     */
    public function vote(Request $request, $id)
    {
        $validated = $request->validate([
            'vote' => 'required|in:helpful,unhelpful',
        ]);

        $review = Review::findOrFail($id);

        // Can't vote on own review
        if ($review->user_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot vote on your own review'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $existingVote = ReviewVote::where('review_id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if ($existingVote) {
                // If same vote, remove it
                if ($existingVote->vote === $validated['vote']) {
                    // Decrement the count
                    if ($existingVote->vote === 'helpful') {
                        $review->decrement('helpful_count');
                    } else {
                        $review->decrement('unhelpful_count');
                    }
                    $existingVote->delete();
                } else {
                    // Change vote
                    if ($existingVote->vote === 'helpful') {
                        $review->decrement('helpful_count');
                        $review->increment('unhelpful_count');
                    } else {
                        $review->decrement('unhelpful_count');
                        $review->increment('helpful_count');
                    }
                    $existingVote->update(['vote' => $validated['vote']]);
                }
            } else {
                // New vote
                ReviewVote::create([
                    'review_id' => $id,
                    'user_id' => Auth::id(),
                    'vote' => $validated['vote'],
                ]);
                
                if ($validated['vote'] === 'helpful') {
                    $review->increment('helpful_count');
                } else {
                    $review->increment('unhelpful_count');
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'helpful_count' => $review->fresh()->helpful_count,
                'unhelpful_count' => $review->fresh()->unhelpful_count,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to register vote'
            ], 500);
        }
    }

    /**
     * Get reviews for a listing (public API)
     */
    public function getListingReviews(Request $request, $listingId)
    {
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort', 'recent'); // recent, helpful, rating_high, rating_low
        
        $query = Review::where('listing_id', $listingId)
            ->where('status', 'approved')
            ->with(['user:id,name']);

        switch ($sortBy) {
            case 'helpful':
                $query->orderBy('helpful_count', 'desc');
                break;
            case 'rating_high':
                $query->orderBy('rating', 'desc');
                break;
            case 'rating_low':
                $query->orderBy('rating', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $reviews = $query->paginate($perPage);
        
        // Get rating distribution
        $distribution = Review::getRatingDistribution($listingId);
        $avgRating = Review::getAverageRating($listingId);
        $totalReviews = Review::getReviewsCount($listingId);

        return response()->json([
            'reviews' => $reviews,
            'stats' => [
                'average_rating' => round($avgRating, 1),
                'total_reviews' => $totalReviews,
                'distribution' => $distribution,
            ],
        ]);
    }

    /**
     * Update listing rating cache (optional optimization)
     */
    private function updateListingRatingCache($listingId)
    {
        // You could store avg rating and count in listings table for performance
        // For now, we'll calculate on the fly
        // Listing::where('id', $listingId)->update([
        //     'average_rating' => Review::getAverageRating($listingId),
        //     'reviews_count' => Review::getReviewsCount($listingId),
        // ]);
    }
}