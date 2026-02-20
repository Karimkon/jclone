<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\PushNotification;
use App\Models\UserNotificationPreference;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PushNotificationService
{
    private string $projectId;
    private array $serviceAccount;

    public function __construct()
    {
        $path = storage_path('app/' . config('services.firebase.service_account_path', 'firebase-service-account.json'));

        if (file_exists($path)) {
            $this->serviceAccount = json_decode(file_get_contents($path), true);
            $this->projectId = $this->serviceAccount['project_id'] ?? config('services.firebase.project_id', 'bebamart-95b69');
        } else {
            $this->serviceAccount = [];
            $this->projectId = config('services.firebase.project_id', 'bebamart-95b69');
        }
    }

    /**
     * Send notification to a specific user
     */
    public function sendToUser(
        int $userId,
        string $type,
        string $title,
        string $body,
        array $data = [],
        ?string $imageUrl = null
    ): ?PushNotification {
        // Check preferences
        $prefs = UserNotificationPreference::getOrCreate($userId);
        if (!$prefs->push_enabled) {
            // Still save for inbox, but don't send push
            return PushNotification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'image_url' => $imageUrl,
                'data' => $data,
                'status' => 'sent', // Saved to inbox
            ]);
        }

        if (!$this->isTypeAllowed($prefs, $type)) {
            return null;
        }

        // Create notification record
        $notification = PushNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'image_url' => $imageUrl,
            'data' => $data,
            'status' => 'pending',
        ]);

        // Get active device tokens
        $tokens = DeviceToken::where('user_id', $userId)->active()->pluck('token');

        if ($tokens->isEmpty()) {
            $notification->update(['status' => 'sent']); // Still saved for inbox
            return $notification;
        }

        // Send to each device
        $sent = false;
        foreach ($tokens as $token) {
            try {
                $this->sendFcmMessage($token, $title, $body, $data, $imageUrl, $userId);
                $sent = true;
            } catch (\Exception $e) {
                // If token is invalid, deactivate it
                if ($this->isInvalidTokenError($e)) {
                    DeviceToken::where('token', $token)->update(['is_active' => false]);
                }
                Log::warning('FCM send failed', [
                    'token' => substr($token, 0, 20) . '...',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $notification->update([
            'status' => $sent ? 'sent' : 'failed',
            'sent_at' => $sent ? now() : null,
            'error_message' => $sent ? null : 'All device tokens failed',
        ]);

        return $notification;
    }

    /**
     * Send bulk notifications
     */
    public function sendBulk(array $notifications): int
    {
        $sentCount = 0;
        foreach ($notifications as $notif) {
            $result = $this->sendToUser(
                $notif['user_id'],
                $notif['type'],
                $notif['title'],
                $notif['body'],
                $notif['data'] ?? [],
                $notif['image_url'] ?? null
            );
            if ($result) {
                $sentCount++;
            }
        }
        return $sentCount;
    }

    /**
     * Send FCM message via HTTP v1 API
     */
    private function sendFcmMessage(
        string $token,
        string $title,
        string $body,
        array $data,
        ?string $imageUrl,
        int $userId
    ): void {
        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => empty($data) ? new \stdClass() : $this->prepareData($data),
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'bebamart_notifications',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'badge' => $this->getUnreadCount($userId),
                            'sound' => 'default',
                        ],
                    ],
                ],
            ],
        ];

        if ($imageUrl) {
            $message['message']['notification']['image'] = $imageUrl;
        }

        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->timeout(10)
            ->post(
                "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
                $message
            );

        if (!$response->successful()) {
            throw new \Exception('FCM Error: ' . $response->body());
        }
    }

    /**
     * FCM data values must all be strings
     */
    private function prepareData(array $data): array
    {
        $prepared = [];
        foreach ($data as $key => $value) {
            $prepared[$key] = is_string($value) ? $value : (string) $value;
        }
        return $prepared;
    }

    /**
     * Get unread notification count for badge
     */
    private function getUnreadCount(int $userId): int
    {
        return PushNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get OAuth2 access token for FCM HTTP v1 API
     * Cached for 50 minutes (tokens expire at 60 min)
     */
    private function getAccessToken(): string
    {
        return Cache::remember('fcm_access_token', 3000, function () {
            if (empty($this->serviceAccount)) {
                throw new \Exception('Firebase service account not configured');
            }

            $now = time();
            $jwt = $this->createJwt($now);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to get FCM access token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Create JWT for Google OAuth2 service account authentication
     */
    private function createJwt(int $now): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ]));

        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $this->serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $signatureInput = "{$header}.{$payload}";

        $privateKey = openssl_pkey_get_private($this->serviceAccount['private_key']);
        openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        $encodedSignature = $this->base64UrlEncode($signature);

        return "{$header}.{$payload}.{$encodedSignature}";
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Check if the FCM error indicates an invalid/expired token
     */
    private function isInvalidTokenError(\Exception $e): bool
    {
        $message = $e->getMessage();
        return str_contains($message, 'UNREGISTERED')
            || str_contains($message, 'NOT_FOUND');
    }

    /**
     * Check if a notification type is allowed by user preferences
     */
    private function isTypeAllowed(UserNotificationPreference $prefs, string $type): bool
    {
        return match ($type) {
            'order_update' => $prefs->order_updates,
            'cart_reminder' => $prefs->cart_reminders,
            'price_drop' => $prefs->price_drops,
            'recommendation' => $prefs->recommendations,
            'promo' => $prefs->promotions,
            'vendor_order' => $prefs->new_orders,
            'vendor_review' => $prefs->reviews,
            'vendor_payout' => $prefs->payouts,
            'vendor_tip' => $prefs->vendor_tips,
            default => true,
        };
    }
}
