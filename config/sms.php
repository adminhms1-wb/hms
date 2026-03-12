<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Provider Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for SMS providers.
    | Supported providers: 'twilio', 'nexmo', 'custom', 'log' (default)
    |
    */

    'enabled' => env('SMS_ENABLED', true),

    'provider' => env('SMS_PROVIDER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Twilio Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Twilio SMS service.
    | Get credentials from: https://www.twilio.com/console
    |
    */

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from_number' => env('TWILIO_FROM_NUMBER'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Nexmo/Vonage Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Nexmo/Vonage SMS service.
    | Get credentials from: https://dashboard.nexmo.com/
    |
    */

    'nexmo' => [
        'api_key' => env('NEXMO_API_KEY'),
        'api_secret' => env('NEXMO_API_SECRET'),
        'from_number' => env('NEXMO_FROM_NUMBER'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom SMS API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for custom SMS API service.
    |
    */

    'custom' => [
        'api_url' => env('CUSTOM_SMS_API_URL'),
        'api_key' => env('CUSTOM_SMS_API_KEY'),
    ],
];


