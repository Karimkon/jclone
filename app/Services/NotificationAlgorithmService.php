<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\DeviceToken;
use App\Models\Listing;
use App\Models\ProductInteraction;
use App\Models\SearchQuery;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationAlgorithmService
{
    private PushNotificationService $pushService;

    // Enticing message templates with emojis
    private array $cartTemplates = [
        "Your cart misses you! 🛒 {product} is still waiting",
        "Don't forget! 😍 {product} is still in your cart",
        "Hey! 👋 {product} won't wait forever. Grab it now!",
        "Still thinking about {product}? 🤔 Complete your order before it's gone!",
        "🔥 {product} is selling fast! Your cart is ready to checkout",
    ];

    private array $priceDropTemplates = [
        "🎉 Price drop! {product} is now {percent}% off!",
        "💰 Great news! {product} just got cheaper",
        "🏷️ Alert! {product} dropped to {price}. Don't miss it!",
        "📉 The price you wanted! {product} is now on sale",
    ];

    private array $searchTemplates = [
        "Still looking for \"{query}\"? 🔎 Check out {product}!",
        "We found something you'll love! 😍 Based on your search for \"{query}\"",
        "New match for \"{query}\"! ✨ {product} just arrived",
    ];

    private array $viewTemplates = [
        "Loved {product}? 👀 It's still available!",
        "Back for more? 😊 {product} is waiting for you",
        "Don't miss out! 🔥 {product} you viewed is trending",
    ];

    private array $trendingTemplates = [
        "🔥 Trending now: {product}. Everyone's buying it!",
        "⭐ Hot deal alert! {product} is a bestseller today",
        "📈 {product} is trending! See why everyone loves it",
    ];

    private array $newArrivalTemplates = [
        "✨ New arrival: {product}! Be the first to grab it",
        "🆕 Just in! {product} — fresh and ready for you",
        "🎊 New drop! {product} just landed on BebaMart",
    ];

    private array $generalTemplates = [
        "🛍️ Great deals await! Check out today's top picks on BebaMart",
        "💫 Your daily dose of amazing deals is here!",
        "🎯 Handpicked deals just for you! Shop now on BebaMart",
        "🌟 Don't miss today's specials on BebaMart!",
        "🤩 Thousands of products. One marketplace. Shop BebaMart now!",
        "💡 Smart shoppers buy on BebaMart — come see why!",
        "🎁 Something great is waiting for you. Come discover it!",
    ];

    private array $reEngagementTemplates = [
        "We miss you! 😢 Come back and see what's new on BebaMart",
        "It's been a while! 👋 Thousands of new deals are waiting for you",
        "BebaMart has been busy! 🛒 New products, better deals — come check",
        "Your perfect product might be waiting! 🔍 Come back and explore",
        "Don't let great deals pass you by! 🏃 BebaMart is calling",
    ];

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Generate and send daily personalized notifications
     * Called by artisan command at 10 AM daily
     */
    public function sendDailyNotifications(): array
    {
        $stats = ['total_users' => 0, 'sent' => 0, 'skipped' => 0];

        // Get users with active device tokens
        $userIds = DeviceToken::where('is_active', true)
            ->distinct()
            ->pluck('user_id');

        $stats['total_users'] = $userIds->count();

        foreach ($userIds as $userId) {
            try {
                // Skip if user already received a push notification today — avoid double-pinging
                // (e.g. an order update fired at 9 AM before this 10 AM daily cron)
                $alreadySentToday = DB::table('push_notifications')
                    ->where('user_id', $userId)
                    ->whereDate('created_at', today())
                    ->where('status', 'sent')
                    ->exists();

                if ($alreadySentToday) {
                    $stats['skipped']++;
                    continue;
                }

                $isVendor = DB::table('vendor_profiles')
                    ->where('user_id', $userId)
                    ->where('vetting_status', 'approved')
                    ->exists();

                $sent = $isVendor
                    ? $this->generateVendorNotification($userId)
                    : $this->generatePersonalizedNotification($userId);

                if ($sent) {
                    $stats['sent']++;
                } else {
                    $stats['skipped']++;
                }
            } catch (\Exception $e) {
                $stats['skipped']++;
            }
        }

        return $stats;
    }

    /**
     * Send cart abandonment reminders
     * Called every 4 hours
     */
    public function sendCartAbandonmentReminders(): array
    {
        $stats = ['sent' => 0, 'skipped' => 0];

        // Quiet hours: never disturb users between 10 PM and 7 AM
        $hour = (int) Carbon::now()->format('H');
        if ($hour >= 22 || $hour < 7) {
            return $stats;
        }

        $fourHoursAgo = Carbon::now()->subHours(4);

        // Find users who added to cart 4+ hours ago but haven't purchased
        $carts = Cart::whereNotNull('user_id')
            ->where('updated_at', '<=', $fourHoursAgo)
            ->whereNotNull('items')
            ->get();

        foreach ($carts as $cart) {
            $items = is_string($cart->items) ? json_decode($cart->items, true) : $cart->items;
            if (empty($items) || !is_array($items) || count($items) === 0) continue;

            // Skip if user completed an order in the past 24 hours (cart may already be fulfilled)
            $recentOrder = DB::table('orders')
                ->where('user_id', $cart->user_id)
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->exists();

            if ($recentOrder) {
                $stats['skipped']++;
                continue;
            }

            // Don't spam — max 1 cart reminder per 24 hours
            $recentReminder = DB::table('push_notifications')
                ->where('user_id', $cart->user_id)
                ->where('type', 'cart_reminder')
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->exists();

            if ($recentReminder) {
                $stats['skipped']++;
                continue;
            }

            // Get first item's listing for the message
            $firstItem = $items[0] ?? null;
            if (!$firstItem) continue;

            $listingId = $firstItem['listing_id'] ?? null;
            if (!$listingId) continue;

            $listing = Listing::with('images')->find($listingId);
            if (!$listing || !$listing->is_active) continue;

            $template = $this->cartTemplates[array_rand($this->cartTemplates)];
            $title = count($items) > 1
                ? "You left " . count($items) . " items in your cart! 🛒"
                : "Complete your purchase! 🛒";
            $body = str_replace('{product}', $this->truncate($listing->title, 40), $template);

            $firstImage = $listing->images->first();
            $imageUrl = $firstImage?->image_url
                ? url('storage/' . $firstImage->image_url)
                : null;

            $this->pushService->sendToUser(
                $cart->user_id,
                'cart_reminder',
                $title,
                $body,
                ['route' => '/cart'],
                $imageUrl
            );

            $stats['sent']++;
        }

        return $stats;
    }

    /**
     * Generate personalized notification for a single user
     * Uses a priority-based strategy selection
     */
    private function generatePersonalizedNotification(int $userId): bool
    {
        // Try strategies in priority order (most relevant first)
        $strategies = [
            [$this, 'tryPriceDropNotification'],
            [$this, 'trySearchBasedNotification'],
            [$this, 'tryViewBasedNotification'],
            [$this, 'tryTrendingNotification'],
            [$this, 'tryNewArrivalNotification'],
            [$this, 'tryReEngagementNotification'], // Win back inactive users
            [$this, 'tryGeneralNotification'],
        ];

        // Shuffle after the first two to add variety on repeated days
        $priorityStrategies = array_slice($strategies, 0, 2);
        $otherStrategies = array_slice($strategies, 2);
        shuffle($otherStrategies);
        $strategies = array_merge($priorityStrategies, $otherStrategies);

        foreach ($strategies as $strategy) {
            if (call_user_func($strategy, $userId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Strategy 1: Price drop on wishlisted items
     */
    private function tryPriceDropNotification(int $userId): bool
    {
        // Get wishlisted items where price has decreased
        $wishlisted = Wishlist::where('user_id', $userId)
            ->pluck('listing_id');

        if ($wishlisted->isEmpty()) return false;

        // Find items where current price < compare_at_price (on sale)
        $onSale = Listing::whereIn('id', $wishlisted)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->whereNotNull('compare_at_price')
            ->whereColumn('price', '<', 'compare_at_price')
            ->with('images')
            ->first();

        if (!$onSale) return false;

        $percent = round((($onSale->compare_at_price - $onSale->price) / $onSale->compare_at_price) * 100);

        $template = $this->priceDropTemplates[array_rand($this->priceDropTemplates)];
        $body = str_replace(
            ['{product}', '{percent}', '{price}'],
            [$this->truncate($onSale->title, 40), $percent, 'UGX ' . number_format($onSale->price)],
            $template
        );

        $firstImage = $onSale->images->first();
        $imageUrl = $firstImage?->image_url
            ? url('storage/' . $firstImage->image_url)
            : null;

        $this->pushService->sendToUser(
            $userId,
            'price_drop',
            "Price drop on your wishlist! 💰",
            $body,
            ['route' => '/product/' . $onSale->id],
            $imageUrl
        );

        return true;
    }

    /**
     * Strategy 2: Based on recent search queries
     */
    private function trySearchBasedNotification(int $userId): bool
    {
        $recentSearch = SearchQuery::where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderByDesc('created_at')
            ->first();

        if (!$recentSearch) return false;

        // Find matching active listings
        $listing = Listing::where('is_active', true)
            ->where('stock', '>', 0)
            ->where('title', 'LIKE', '%' . $recentSearch->query . '%')
            ->with('images')
            ->orderByDesc('view_count')
            ->first();

        if (!$listing) return false;

        $template = $this->searchTemplates[array_rand($this->searchTemplates)];
        $body = str_replace(
            ['{query}', '{product}'],
            [$this->truncate($recentSearch->query, 25), $this->truncate($listing->title, 40)],
            $template
        );

        $firstImage = $listing->images->first();
        $imageUrl = $firstImage?->image_url
            ? url('storage/' . $firstImage->image_url)
            : null;

        $this->pushService->sendToUser(
            $userId,
            'recommendation',
            "Found something for you! 🔍",
            $body,
            ['route' => '/product/' . $listing->id],
            $imageUrl
        );

        return true;
    }

    /**
     * Strategy 3: Based on recently viewed products/categories
     */
    private function tryViewBasedNotification(int $userId): bool
    {
        // Get category IDs the user has interacted with recently
        $viewedListingIds = ProductInteraction::where('user_id', $userId)
            ->whereIn('type', ['view', 'click'])
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->distinct()
            ->pluck('listing_id');

        if ($viewedListingIds->isEmpty()) return false;

        // Get categories from viewed listings
        $categoryIds = Listing::whereIn('id', $viewedListingIds)
            ->distinct()
            ->pluck('category_id')
            ->filter();

        if ($categoryIds->isEmpty()) return false;

        // Find popular products in those categories that the user hasn't viewed
        $listing = Listing::whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $viewedListingIds)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->with('images')
            ->orderByDesc('view_count')
            ->first();

        if (!$listing) {
            // Fallback: recommend one they viewed that's still in stock
            $listing = Listing::whereIn('id', $viewedListingIds->take(10))
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->with('images')
                ->inRandomOrder()
                ->first();
        }

        if (!$listing) return false;

        $template = $this->viewTemplates[array_rand($this->viewTemplates)];
        $body = str_replace('{product}', $this->truncate($listing->title, 40), $template);

        $firstImage = $listing->images->first();
        $imageUrl = $firstImage?->image_url
            ? url('storage/' . $firstImage->image_url)
            : null;

        $this->pushService->sendToUser(
            $userId,
            'recommendation',
            "Picked for you! 🎯",
            $body,
            ['route' => '/product/' . $listing->id],
            $imageUrl
        );

        return true;
    }

    /**
     * Strategy 4: Trending products
     */
    private function tryTrendingNotification(int $userId): bool
    {
        $trending = Listing::where('is_active', true)
            ->where('stock', '>', 0)
            ->where('view_count', '>', 10)
            ->with('images')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get();

        if ($trending->isEmpty()) return false;

        $listing = $trending->random();

        $template = $this->trendingTemplates[array_rand($this->trendingTemplates)];
        $body = str_replace('{product}', $this->truncate($listing->title, 40), $template);

        $firstImage = $listing->images->first();
        $imageUrl = $firstImage?->image_url
            ? url('storage/' . $firstImage->image_url)
            : null;

        $this->pushService->sendToUser(
            $userId,
            'promo',
            "Trending on BebaMart 🔥",
            $body,
            ['route' => '/product/' . $listing->id],
            $imageUrl
        );

        return true;
    }

    /**
     * Strategy 5: New arrivals in browsed categories
     */
    private function tryNewArrivalNotification(int $userId): bool
    {
        // Get categories user has interacted with
        $categoryIds = ProductInteraction::where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->join('listings', 'product_interactions.listing_id', '=', 'listings.id')
            ->distinct()
            ->pluck('listings.category_id')
            ->filter();

        if ($categoryIds->isEmpty()) {
            // Fall back to all recent arrivals
            $categoryIds = null;
        }

        $query = Listing::where('is_active', true)
            ->where('stock', '>', 0)
            ->where('created_at', '>=', Carbon::now()->subDays(3))
            ->with('images')
            ->orderByDesc('created_at');

        if ($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        }

        $listing = $query->first();

        if (!$listing) return false;

        $template = $this->newArrivalTemplates[array_rand($this->newArrivalTemplates)];
        $body = str_replace('{product}', $this->truncate($listing->title, 40), $template);

        $firstImage = $listing->images->first();
        $imageUrl = $firstImage?->image_url
            ? url('storage/' . $firstImage->image_url)
            : null;

        $this->pushService->sendToUser(
            $userId,
            'recommendation',
            "New arrival! ✨",
            $body,
            ['route' => '/product/' . $listing->id],
            $imageUrl
        );

        return true;
    }

    /**
     * Strategy 6: Re-engagement for users who haven't been active in 7+ days
     */
    private function tryReEngagementNotification(int $userId): bool
    {
        // Only fire for genuinely inactive users — don't use it on active ones
        $recentActivity = ProductInteraction::where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->exists();

        if ($recentActivity) return false;

        // Pick a bestselling product as the hook
        $listing = Listing::where('is_active', true)
            ->where('stock', '>', 0)
            ->where('purchase_count', '>', 0)
            ->with('images')
            ->orderByDesc('purchase_count')
            ->limit(20)
            ->get()
            ->random();

        if (!$listing) return false;

        $firstImage = $listing->images->first();
        $imageUrl = $firstImage?->image_url
            ? url('storage/' . $firstImage->image_url)
            : null;

        $this->pushService->sendToUser(
            $userId,
            'recommendation',
            "BebaMart misses you! 💙",
            $this->reEngagementTemplates[array_rand($this->reEngagementTemplates)],
            ['route' => '/home'],
            $imageUrl
        );

        return true;
    }

    /**
     * Strategy 7: General promotional notification (fallback)
     */
    private function tryGeneralNotification(int $userId): bool
    {
        $body = $this->generalTemplates[array_rand($this->generalTemplates)];

        $this->pushService->sendToUser(
            $userId,
            'promo',
            "BebaMart 🛍️",
            $body,
            ['route' => '/home']
        );

        return true;
    }

    /**
     * Generate vendor-specific notification (store performance, tips)
     */
    private function generateVendorNotification(int $userId): bool
    {
        $hour = (int) now()->format('H');

        // Morning: motivate with store stats
        if ($hour >= 6 && $hour < 12) {
            $views = DB::table('listings')
                ->where('vendor_id', function($q) use ($userId) {
                    $q->select('id')->from('vendor_profiles')->where('user_id', $userId);
                })
                ->sum('view_count');

            $titles = ["Good morning! ☀️", "Rise & sell! 🚀", "Start strong today! 💪"];
            $bodies = [
                "Your listings have {views} total views. Keep them fresh to attract buyers!",
                "Morning check-in: {views} views on your products. Add new listings to boost sales!",
                "Your store has been viewed {views} times. Today is a great day to add new products!",
            ];
            $title = $titles[array_rand($titles)];
            $body = str_replace('{views}', number_format($views), $bodies[array_rand($bodies)]);
            $route = '/vendor/dashboard';
        }
        // Afternoon: check orders
        elseif ($hour >= 12 && $hour < 18) {
            $pendingOrders = DB::table('orders')
                ->where('vendor_id', function($q) use ($userId) {
                    $q->select('id')->from('vendor_profiles')->where('user_id', $userId);
                })
                ->where('status', 'pending')
                ->count();

            if ($pendingOrders > 0) {
                $title = "Orders need attention! 📦";
                $body = "You have {$pendingOrders} pending order" . ($pendingOrders > 1 ? 's' : '') . " waiting to be processed.";
                $route = '/vendor/orders';
            } else {
                $title = "Boost your visibility! 🎯";
                $body = "No pending orders? Add new products or update prices to attract more buyers.";
                $route = '/vendor/products';
            }
        }
        // Evening: recap
        else {
            $todaySales = DB::table('orders')
                ->where('vendor_id', function($q) use ($userId) {
                    $q->select('id')->from('vendor_profiles')->where('user_id', $userId);
                })
                ->whereDate('created_at', today())
                ->count();

            $titles = ["Evening recap 🌙", "Today's summary 📊", "End of day check-in ✅"];
            $title = $titles[array_rand($titles)];
            $body = $todaySales > 0
                ? "Great day! You received {$todaySales} order" . ($todaySales > 1 ? 's' : '') . " today. Keep it up!"
                : "No orders today yet. Try updating your product photos or prices to stand out!";
            $route = '/vendor/dashboard';
        }

        $this->pushService->sendToUser(
            $userId,
            'recommendation',
            $title,
            $body,
            ['route' => $route]
        );

        return true;
    }

    /**
     * Truncate string to max length
     */
    private function truncate(string $text, int $maxLength): string
    {
        if (mb_strlen($text) <= $maxLength) return $text;
        return mb_substr($text, 0, $maxLength - 3) . '...';
    }
}
