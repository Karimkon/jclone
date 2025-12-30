<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EgoSmsService
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $senderId;

    public function __construct()
    {
        $this->baseUrl = 'https://www.egosms.co/api/v1/json/';
        $this->username = config('services.egosms.username');
        $this->password = config('services.egosms.password');
        $this->senderId = config('services.egosms.sender_id', 'BEBAMART');
    }

    /**
     * Send SMS
     */
    public function sendSms($number, $message, $priority = '0')
    {
        $formattedNumber = $this->formatPhoneNumber($number);

        if (!$formattedNumber) {
            Log::error('Invalid phone number format', ['number' => $number]);
            return ['Status' => 'Failed', 'Message' => 'Invalid phone number format'];
        }

        // Truncate message to 160 characters if longer
        if (strlen($message) > 160) {
            $message = substr($message, 0, 157) . '...';
        }

        $data = [
            'method' => 'SendSms',
            'userdata' => [
                'username' => $this->username,
                'password' => $this->password
            ],
            'msgdata' => [
                [
                    'number' => $formattedNumber,
                    'message' => $message,
                    'senderid' => $this->senderId,
                    'priority' => $priority
                ]
            ]
        ];

        return $this->makeRequest($data);
    }

    /**
     * Send OTP via SMS
     */
    public function sendOtp($number, $otp)
    {
        $message = "Your BebaMart verification code is: {$otp}. This code expires in 10 minutes. Do not share this code with anyone.";
        return $this->sendSms($number, $message, '1'); // High priority for OTP
    }

    /**
     * Make HTTP request to EgoSMS API
     */
    private function makeRequest($data)
    {
        try {
            // Check cache for recent failures to avoid spamming API
            $cacheKey = 'egosms_last_failure';
            if (Cache::has($cacheKey)) {
                $lastFailure = Cache::get($cacheKey);
                if (now()->diffInMinutes($lastFailure) < 5) {
                    Log::warning('EgoSMS API recently failed, skipping request');
                    return ['Status' => 'Failed', 'Message' => 'API temporarily unavailable'];
                }
            }

            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($this->baseUrl, $data);

            if (!$response->successful()) {
                Cache::put($cacheKey, now(), 5);
                Log::error('EgoSMS API HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [
                    'Status' => 'Failed',
                    'Message' => 'HTTP error: ' . $response->status()
                ];
            }

            $result = $response->json();

            Log::info('EgoSMS API Response', [
                'status' => $result['Status'] ?? 'Unknown',
                'message' => $result['Message'] ?? 'No message',
            ]);

            Cache::forget($cacheKey);

            return $result;

        } catch (\Exception $e) {
            Cache::put($cacheKey, now(), 5);
            Log::error('EgoSMS API Exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'Status' => 'Failed',
                'Message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number to EgoSMS format (256...)
     */
    private function formatPhoneNumber($number)
    {
        if (empty($number)) {
            return false;
        }

        // Remove all non-digits
        $number = preg_replace('/\D/', '', $number);

        // Handle various formats
        if (substr($number, 0, 1) === '0') {
            $number = '256' . substr($number, 1);
        }

        if (substr($number, 0, 4) === '2560') {
            $number = '256' . substr($number, 4);
        }

        if (substr($number, 0, 1) === '+') {
            $number = substr($number, 1);
        }

        // If just 9 digits (local format without country code)
        if (strlen($number) === 9 && substr($number, 0, 1) !== '0') {
            $number = '256' . $number;
        }

        // Validate final format: 256 followed by 9 digits
        if (!preg_match('/^256[1-9][0-9]{8}$/', $number)) {
            return false;
        }

        return $number;
    }

    /**
     * Check if SMS was sent successfully
     */
    public function isSuccess($response)
    {
        return isset($response['Status']) &&
               strtoupper($response['Status']) === 'OK' &&
               (!isset($response['Error']) || $response['Error'] == 0);
    }

    /**
     * Get remaining SMS balance
     */
    public function getBalance()
    {
        $data = [
            'method' => 'GetBalance',
            'userdata' => [
                'username' => $this->username,
                'password' => $this->password
            ]
        ];

        try {
            $response = Http::post($this->baseUrl, $data);
            $result = $response->json();

            if (isset($result['Balance'])) {
                return (float) $result['Balance'];
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Failed to get SMS balance', ['error' => $e->getMessage()]);
            return 0;
        }
    }
}
