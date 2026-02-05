<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

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
     * Get ranked listings from a query
     *
     * @param Builder $query
     * @param int $limit
     * @return Collection
     */
    public function getRankedListings(Builder $query, int $limit = 20): Collection
    {
        // Get listings with vendor subscription data eagerly loaded
        $listings = $query->with([
            'vendor.activeSubscription.plan',
            'images',
            'reviews',
        ])->get();

        // Calculate scores and sort
        return $listings->map(function ($listing) {
            $listing->organic_score = $this->calculateOrganicScore($listing);
            $listing->ranking_score = $this->calculateFinalScore($listing);
            $listing->boost_multiplier = $this->getBoostMultiplier($listing);
            return $listing;
        })
        ->sortByDesc('ranking_score')
        ->take($limit)
        ->values();
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
    public function searchWithRanking(Builder $query, ?string $searchTerm = null, int $limit = 20): Collection
    {
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
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
    public function getFeaturedListings(int $limit = 10): Collection
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
    public function getListingsByCategory(int $categoryId, int $limit = 20): Collection
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
