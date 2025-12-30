<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Flutterwave Configuration (Cards & Bank Transfers)
    |--------------------------------------------------------------------------
    */
    'flutterwave' => [
        'public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),
        'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),
        'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
        'secret_hash' => env('FLUTTERWAVE_SECRET_HASH'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PesaPal Configuration (Mobile Money)
    |--------------------------------------------------------------------------
    |
    | PesaPal supports MTN Mobile Money and Airtel Money in Uganda.
    | 
    | IMPORTANT: 
    | - Production URL: https://pay.pesapal.com/v3
    | - Sandbox URL: https://cybqa.pesapal.com/pesapalv3
    |
    | Get your credentials from: https://developer.pesapal.com
    |
    */
    'pesapal' => [
        'base_url' => env('PESAPAL_BASE_URL', 'https://pay.pesapal.com/v3'),
        'consumer_key' => env('PESAPAL_CONSUMER_KEY'),
        'consumer_secret' => env('PESAPAL_CONSUMER_SECRET'),
        'notification_id' => env('PESAPAL_NOTIFICATION_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | EgoSMS Configuration (SMS OTP Verification)
    |--------------------------------------------------------------------------
    |
    | EgoSMS is used for sending SMS OTP codes for phone verification.
    | Get your credentials from: https://www.egosms.co
    |
    */
    'egosms' => [
        'username' => env('EGOSMS_USERNAME'),
        'password' => env('EGOSMS_PASSWORD'),
        'sender_id' => env('EGOSMS_SENDER_ID', 'BEBAMART'),
    ],

];