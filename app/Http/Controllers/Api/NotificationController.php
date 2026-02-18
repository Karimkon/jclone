<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\PushNotification;
use App\Models\UserNotificationPreference;
use App\Models\SearchQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Register or update FCM device token
     * POST /api/device-token
     */
    public function registerToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:500',
            'platform' => 'in:android,ios,web',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        // Upsert: update if token exists, create if not
        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $user->id,
                'platform' => $request->platform ?? 'android',
                'device_name' => $request->device_name,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device token registered',
        ]);
    }

    /**
     * Remove FCM device token (on logout)
     * DELETE /api/device-token
     */
    public function removeToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        DeviceToken::where('token', $request->token)
            ->where('user_id', Auth::id())
            ->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Device token removed',
        ]);
    }

    /**
     * Get notification inbox (paginated)
     * GET /api/notifications
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $notifications = PushNotification::where('user_id', Auth::id())
            ->whereIn('status', ['sent', 'pending'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'notifications' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Mark a notification as read
     * POST /api/notifications/{id}/read
     */
    public function markRead($id)
    {
        $notification = PushNotification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     * POST /api/notifications/read-all
     */
    public function markAllRead()
    {
        PushNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Get unread notification count (for badge)
     * GET /api/notifications/unread-count
     */
    public function unreadCount()
    {
        $count = PushNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }

    /**
     * Get user notification preferences
     * GET /api/notification-preferences
     */
    public function getPreferences()
    {
        $prefs = UserNotificationPreference::getOrCreate(Auth::id());

        return response()->json([
            'success' => true,
            'preferences' => $prefs,
        ]);
    }

    /**
     * Update user notification preferences
     * PUT /api/notification-preferences
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'push_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'order_updates' => 'boolean',
            'promotions' => 'boolean',
            'recommendations' => 'boolean',
            'price_drops' => 'boolean',
            'cart_reminders' => 'boolean',
            'new_orders' => 'boolean',
            'reviews' => 'boolean',
            'payouts' => 'boolean',
            'vendor_tips' => 'boolean',
        ]);

        $prefs = UserNotificationPreference::getOrCreate(Auth::id());
        $prefs->update($request->only([
            'push_enabled', 'email_enabled',
            'order_updates', 'promotions', 'recommendations',
            'price_drops', 'cart_reminders',
            'new_orders', 'reviews', 'payouts', 'vendor_tips',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated',
            'preferences' => $prefs->fresh(),
        ]);
    }

    /**
     * Log a search query (for personalized notifications)
     * POST /api/search-queries
     */
    public function logSearch(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255',
            'results_count' => 'integer|min:0',
        ]);

        SearchQuery::create([
            'user_id' => Auth::id(),
            'query' => $request->input('query'),
            'results_count' => $request->input('results_count', 0),
            'source' => 'app',
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
