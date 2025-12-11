<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PesapalService
{
    protected $baseUrl;
    protected $consumerKey;
    protected $consumerSecret;
    protected $notificationId;

    public function __construct()
    {
        $this->baseUrl = config('services.pesapal.base_url', 'https://pay.pesapal.com/v3');
        $this->consumerKey = config('services.pesapal.consumer_key');
        $this->consumerSecret = config('services.pesapal.consumer_secret');
        $this->notificationId = config('services.pesapal.notification_id');
        
        // Debug: Log configuration on construction
        Log::debug('PesaPal Service initialized', [
            'base_url' => $this->baseUrl,
            'consumer_key' => $this->consumerKey ? substr($this->consumerKey, 0, 10) . '...' : 'NOT SET',
            'consumer_secret' => $this->consumerSecret ? 'SET' : 'NOT SET',
            'notification_id' => $this->notificationId ?? 'NOT SET',
        ]);
    }

    /**
     * Get access token from Pesapal (with caching)
     */
    public function getAccessToken(): ?string
    {
        // Clear cache in debug mode to always get fresh token
        if (config('app.debug')) {
            Cache::forget('pesapal_access_token');
        }
        
        return Cache::remember('pesapal_access_token', 240, function () {
            try {
                Log::info('PesaPal: Requesting access token...', [
                    'url' => $this->baseUrl . '/api/Auth/RequestToken',
                    'consumer_key' => $this->consumerKey ? substr($this->consumerKey, 0, 10) . '...' : 'EMPTY',
                ]);

                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl . '/api/Auth/RequestToken', [
                    'consumer_key' => $this->consumerKey,
                    'consumer_secret' => $this->consumerSecret,
                ]);

                Log::info('PesaPal Auth Response', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['token'])) {
                        Log::info('PesaPal token obtained successfully');
                        return $data['token'];
                    }
                    Log::error('PesaPal Auth: Token not in response', $data);
                    return null;
                }

                Log::error('PesaPal Auth Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            } catch (\Exception $e) {
                Log::error('PesaPal Auth Exception: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Register IPN (Instant Payment Notification) URL
     * You only need to do this ONCE per callback URL
     */
    public function registerIPN(string $url, string $type = 'GET'): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            throw new \Exception('Failed to get PesaPal access token');
        }

        try {
            Log::info('PesaPal: Registering IPN URL', ['url' => $url, 'type' => $type]);

            $response = Http::withToken($token)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->post($this->baseUrl . '/api/URLSetup/RegisterIPN', [
                    'url' => $url,
                    'ipn_notification_type' => $type,
                ]);

            Log::info('PesaPal IPN Registration Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('PesaPal IPN registered successfully', $data);
                return $data;
            }

            Log::error('PesaPal IPN Registration Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('PesaPal IPN Registration Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get registered IPN URLs
     */
    public function getRegisteredIPNs(): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            throw new \Exception('Failed to get PesaPal access token');
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->get($this->baseUrl . '/api/URLSetup/GetIpnList');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('PesaPal Get IPNs Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('PesaPal Get IPNs Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Submit order to Pesapal for Mobile Money payment
     * 
     * IMPORTANT: PesaPal workflow:
     * 1. Submit order -> Get redirect_url
     * 2. Redirect user to redirect_url (PesaPal's hosted page)
     * 3. User enters phone number on PesaPal's page
     * 4. PesaPal sends USSD prompt to user's phone
     * 5. User authorizes payment on their phone
     * 6. PesaPal redirects back to your callback_url
     */
    public function submitOrder(array $orderData): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            throw new \Exception('Failed to get PesaPal access token. Check your PESAPAL_CONSUMER_KEY and PESAPAL_CONSUMER_SECRET in .env');
        }

        // Ensure notification_id is set
        if (!isset($orderData['notification_id']) || empty($orderData['notification_id'])) {
            $orderData['notification_id'] = $this->notificationId;
        }
        
        // Validate required fields
        if (empty($orderData['notification_id'])) {
            throw new \Exception('PESAPAL_NOTIFICATION_ID is not set. Please register an IPN URL first.');
        }

        try {
            Log::info('PesaPal: Submitting order', [
                'order_id' => $orderData['id'] ?? 'N/A',
                'amount' => $orderData['amount'] ?? 'N/A',
                'currency' => $orderData['currency'] ?? 'N/A',
                'callback_url' => $orderData['callback_url'] ?? 'N/A',
                'notification_id' => $orderData['notification_id'],
            ]);

            $response = Http::withToken($token)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->post($this->baseUrl . '/api/Transactions/SubmitOrderRequest', $orderData);

            $responseData = $response->json();
            
            Log::info('PesaPal submitOrder response', [
                'status' => $response->status(),
                'body' => $responseData,
            ]);

            if ($response->successful()) {
                if (isset($responseData['redirect_url'])) {
                    Log::info('PesaPal order submitted successfully', [
                        'redirect_url' => $responseData['redirect_url'],
                        'order_tracking_id' => $responseData['order_tracking_id'] ?? null,
                    ]);
                }
                return $responseData;
            }

            // Handle specific error codes
            $errorMessage = $responseData['error']['message'] ?? $responseData['message'] ?? $response->body();
            Log::error('PesaPal Order Failed', [
                'status' => $response->status(),
                'error' => $errorMessage,
                'full_response' => $responseData,
            ]);
            
            throw new \Exception('PesaPal order submission failed: ' . $errorMessage);
        } catch (\Exception $e) {
            Log::error('PesaPal Order Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get transaction status from Pesapal
     */
    public function getTransactionStatus(string $orderTrackingId): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            throw new \Exception('Failed to get PesaPal access token');
        }

        try {
            Log::info('PesaPal: Checking transaction status', ['orderTrackingId' => $orderTrackingId]);

            $response = Http::withToken($token)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->get($this->baseUrl . '/api/Transactions/GetTransactionStatus', [
                    'orderTrackingId' => $orderTrackingId
                ]);

            Log::info('PesaPal Transaction Status Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('PesaPal Status Check Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('PesaPal Status Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if payment is successful based on status code
     * Status codes: 0 = Invalid, 1 = Completed, 2 = Failed, 3 = Reversed
     */
    public function isPaymentSuccessful(array $status): bool
    {
        return isset($status['status_code']) && $status['status_code'] == 1;
    }

    /**
     * Get payment status description
     */
    public function getStatusDescription(int $statusCode): string
    {
        return match($statusCode) {
            0 => 'Invalid',
            1 => 'Completed',
            2 => 'Failed',
            3 => 'Reversed',
            default => 'Unknown'
        };
    }

    /**
     * Debug method to test connection
     */
    public function testConnection(): array
    {
        $result = [
            'config_ok' => false,
            'auth_ok' => false,
            'ipn_registered' => false,
            'errors' => [],
        ];

        // Check config
        if (empty($this->consumerKey)) {
            $result['errors'][] = 'PESAPAL_CONSUMER_KEY is not set in .env';
        }
        if (empty($this->consumerSecret)) {
            $result['errors'][] = 'PESAPAL_CONSUMER_SECRET is not set in .env';
        }
        if (empty($this->notificationId)) {
            $result['errors'][] = 'PESAPAL_NOTIFICATION_ID is not set in .env (register IPN first)';
        }
        
        $result['config_ok'] = empty($result['errors']);

        // Test authentication
        if ($result['config_ok']) {
            try {
                $token = $this->getAccessToken();
                $result['auth_ok'] = !empty($token);
                if (!$result['auth_ok']) {
                    $result['errors'][] = 'Failed to get access token - check your credentials';
                }
            } catch (\Exception $e) {
                $result['errors'][] = 'Auth error: ' . $e->getMessage();
            }
        }

        // Check IPNs
        if ($result['auth_ok']) {
            try {
                $ipns = $this->getRegisteredIPNs();
                $result['ipn_registered'] = !empty($ipns);
                $result['registered_ipns'] = $ipns;
            } catch (\Exception $e) {
                $result['errors'][] = 'IPN check error: ' . $e->getMessage();
            }
        }

        return $result;
    }
}