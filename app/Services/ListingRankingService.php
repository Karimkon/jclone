<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as BaseCollection;

class ListingRankingService
{
    /**
     * Weight factors for different ranking components
     */
    protected const WEIGHTS = [
        'rating' => 25,          // Max 25 points for rating
        'reviews' => 20,         // Max 20 points for review count
        'verification' => 10,    // 10 points if vendor is verified
        'recency' => 15,         // Max 15 points for newer listings
        'sales_velocity' => 20,  // Max 20 points for recent sales
        'stock' => 10,           // 10 points if in stock
    ];

    /**
     * Calculate organic score for a listing
     *
     * @param Listing $listing
     * @return float Score from 0-100
     */
    public function calculateOrganicScore(Listing $listing): float
    {
        $score = 0;

        // Rating factor (0-25 points) - based on average rating
        $rating = $listing->average_rating ?? 0;
        $score += ($rating / 5) * self::WEIGHTS['rating'];

        // Reviews count factor (0-20 points) - capped at 100 reviews
        $reviewsCount = $listing->reviews_count ?? 0;
        $score += min($reviewsCount, 100) / 100 * self::WEIGHTS['reviews'];

        // Vendor verification factor (10 points)
        if ($listing->vendor && $listing->vendor->vetting_status === 'approved') {
            $score += self::WEIGHTS['verification'];
        }

        // Recency factor (0-15 points) - newer listings score higher
        $daysSinceCreated = $listing->created_at->diffInDays(now());
        $recencyScore = max(0, self::WEIGHTS['recency'] - ($daysSinceCreated / 30));
        $score += $recencyScore;

        // Sales velocity (0-20 points) - orders in last 30 days
        $recentSales = $this->getRecentSalesCount($listing);
        $score += min($recentSales, 50) / 50 * self::WEIGHTS['sales_velocity'];

        // Stock availability (10 points)
        if ($listing->stock > 0) {
            $score += self::WEIGHTS['stock'];
        }

        return round($score, 2);
    }

    /**
     * Calculate final score with subscription boost
     *
     * @param Listing $listing
     * @return float Final score with boost multiplier applied
     */
    public function calculateFinalScore(Listing $listing): float
    {
        $organicScore = $this->calculateOrganicScore($listing);
        $boostMultiplier = $this->getBoostMultiplier($listing);

        return round($organicScore * $boostMultiplier, 2);
    }

    /**
     * Get boost multiplier from vendor's subscription
     *
     * @param Listing $listing
     * @return float
     */
    protected function getBoostMultiplier(Listing $listing): float
    {
        if (!$listing->vendor) {
            return 1.0;
        }

        // Check if method exists (subscription system may not be deployed)
        if (method_exists($listing->vendor, 'getBoostMultiplier')) {
            return $listing->vendor->getBoostMultiplier();
        }

        return 1.0;
    }

    /**
     * Get recent sales count for a listing
     *
     * @param Listing $listing
     * @return int
     */
    protected function getRecentSalesCount(Listing $listing): int
    {
        return $listing->interactions()
            ->where('interaction_type', 'purchase')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
    }

    /**
     * Fair exposure ratio - ensures free vendors get at least this percentage of visibility
     * For every 10 products, at least 3 will be from free vendors (if available)
     */
    protected const FREE_VENDOR_RATIO = 0.30; // 30% guaranteed for free vendors

    /**
     * Get ranked listings from a query with fair exposure
     *
     * @param Builder $query
     * @param int $limit
     * @return Collection
     */
    public function getRankedListings(Builder $query, int $limit = 20): BaseCollection
    {
        // Get listings - try to load subscription data, but don't fail if table doesn't exist
        try {
            $listings = $query->with([
                'vendor.activeSubscription.plan',
                'images',
                'reviews',
            ])->get();
        } catch (\Exception $e) {
            // Subscription tables might not exist, load without them
            $listings = $query->with(['vendor', 'images', 'reviews'])->get();
        }

        // Calculate scores for all listings
        $scoredListings = $listings->map(function ($listing) {
            try {
                $listing->organic_score = $this->calculateOrganicScore($listing);
                $listing->ranking_score = $this->calculateFinalScore($listing);
                $listing->boost_multiplier = $this->getBoostMultiplier($listing);
                $listing->is_boosted = $listing->boost_multiplier > 1.0;
            } catch (\Exception $e) {
                // If scoring fails, use defaults
                $listing->organic_score = 50;
                $listing->ranking_score = 50;
                $listing->boost_multiplier = 1.0;
                $listing->is_boosted = false;
            }
            return $listing;
        });

        // Separate boosted (paid) and non-boosted (free) listings
        $boostedListings = $scoredListings->filter(fn($l) => $l->is_boosted)->sortByDesc('ranking_score');
        $freeListings = $scoredListings->filter(fn($l) => !$l->is_boosted)->sortByDesc('organic_score');

        // Apply fair exposure: interleave free vendors to guarantee visibility
        return $this->interleaveWithFairExposure($boostedListings, $freeListings, $limit);
    }

    /**
     * Interleave paid and free listings to ensure fair exposure
     * Free vendors get at least 30% of slots (configurable via FREE_VENDOR_RATIO)
     *
     * @param Collection $boostedListings
     * @param Collection $freeListings
     * @param int $limit
     * @return Collection
     */
    protected function interleaveWithFairExposure(Collection $boostedListings, Collection $freeListings, int $limit): BaseCollection
    {
        $result = collect();
        $boostedIndex = 0;
        $freeIndex = 0;
        $boostedCount = $boostedListings->count();
        $freeCount = $freeListings->count();

        // Calculate how many free slots we need to guarantee
        $guaranteedFreeSlots = (int) ceil($limit * self::FREE_VENDOR_RATIO);
        $freeSlotInterval = $guaranteedFreeSlots > 0 ? max(1, (int) floor($limit / $guaranteedFreeSlots)) : $limit + 1;
        $freeSlotsUsed = 0;

        for ($i = 0; $i < $limit; $i++) {
            // Every Nth position (e.g., every 3rd), insert a free vendor listing
            $shouldInsertFree = ($i > 0 && $i % $freeSlotInterval === 0 && $freeSlotsUsed < $guaranteedFreeSlots);

            if ($shouldInsertFree && $freeIndex < $freeCount) {
                // Insert free vendor listing
                $result->push($freeListings->values()[$freeIndex]);
                $freeIndex++;
                $freeSlotsUsed++;
            } elseif ($boostedIndex < $boostedCount) {
                // Insert boosted (paid) listing
                $result->push($boostedListings->values()[$boostedIndex]);
                $boostedIndex++;
            } elseif ($freeIndex < $freeCount) {
                // No more boosted listings, fill with free
                $result->push($freeListings->values()[$freeIndex]);
                $freeIndex++;
            } else {
                // No more listings available
                break;
            }
        }

        return $result->values();
    }

    /**
     * Get ranked listings with pagination
     *
     * @param Builder $query
     * @param int $perPage
     * @param int $page
     * @return array
     */
    public function getRankedListingsPaginated(Builder $query, int $perPage = 20, int $page = 1): array
    {
        // Get all listings with scores
        $allListings = $this->getRankedListings($query, PHP_INT_MAX);

        $total = $allListings->count();
        $offset = ($page - 1) * $perPage;
        $items = $allListings->slice($offset, $perPage)->values();

        return [
            'data' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Apply ranking to search results
     *
     * @param Builder $query
     * @param string|null $searchTerm
     * @param int $limit
     * @return Collection
     */
    public function searchWithRanking(Builder $query, ?string $searchTerm = null, int $limit = 20): BaseCollection
    {
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('vendor', function ($vq) use ($searchTerm) {
                      $vq->where('business_name', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        return $this->getRankedListings($query, $limit);
    }

    /**
     * Get featured listings (prioritize paid subscriptions)
     *
     * @param int $limit
     * @return Collection
     */
    public function getFeaturedListings(int $limit = 10): BaseCollection
    {
        $query = Listing::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->whereHas('vendor', function ($q) {
                $q->where('vetting_status', 'approved');
            });

        return $this->getRankedListings($query, $limit);
    }

    /**
     * Get listings by category with ranking
     *
     * @param int $categoryId
     * @param int $limit
     * @return Collection
     */
    public function getListingsByCategory(int $categoryId, int $limit = 20): BaseCollection
    {
        $query = Listing::query()
            ->where('is_active', true)
            ->where('category_id', $categoryId)
            ->whereHas('vendor', function ($q) {
                $q->where('vetting_status', 'approved');
            });

        return $this->getRankedListings($query, $limit);
    }

    /**
     * Get score breakdown for a listing (useful for debugging/admin)
     *
     * @param Listing $listing
     * @return array
     */
    public function getScoreBreakdown(Listing $listing): array
    {
        $rating = $listing->average_rating ?? 0;
        $reviewsCount = $listing->reviews_count ?? 0;
        $daysSinceCreated = $listing->created_at->diffInDays(now());
        $recentSales = $this->getRecentSalesCount($listing);
        $isVerified = $listing->vendor && $listing->vendor->vetting_status === 'approved';
        $inStock = $listing->stock > 0;

        return [
            'rating' => [
                'value' => $rating,
                'points' => round(($rating / 5) * self::WEIGHTS['rating'], 2),
                'max_points' => self::WEIGHTS['rating'],
            ],
            'reviews' => [
                'value' => $reviewsCount,
                'points' => round(min($reviewsCount, 100) / 100 * self::WEIGHTS['reviews'], 2),
                'max_points' => self::WEIGHTS['reviews'],
            ],
            'verification' => [
                'value' => $isVerified,
                'points' => $isVerified ? self::WEIGHTS['verification'] : 0,
                'max_points' => self::WEIGHTS['verification'],
            ],
            'recency' => [
                'value' => $daysSinceCreated . ' days',
                'points' => round(max(0, self::WEIGHTS['recency'] - ($daysSinceCreated / 30)), 2),
                'max_points' => self::WEIGHTS['recency'],
            ],
            'sales_velocity' => [
                'value' => $recentSales,
                'points' => round(min($recentSales, 50) / 50 * self::WEIGHTS['sales_velocity'], 2),
                'max_points' => self::WEIGHTS['sales_velocity'],
            ],
            'stock' => [
                'value' => $listing->stock,
                'points' => $inStock ? self::WEIGHTS['stock'] : 0,
                'max_points' => self::WEIGHTS['stock'],
            ],
            'organic_score' => $this->calculateOrganicScore($listing),
            'boost_multiplier' => $this->getBoostMultiplier($listing),
            'final_score' => $this->calculateFinalScore($listing),
        ];
    }
}
