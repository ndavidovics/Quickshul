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

    'brevo' => [
        'key' => env('BREVO_API_KEY'),
    ],

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'platform_gmail' => [
        'access_token'  => env('PLATFORM_GMAIL_ACCESS_TOKEN'),
        'refresh_token' => env('PLATFORM_GMAIL_REFRESH_TOKEN'),
        'email'         => env('PLATFORM_GMAIL_EMAIL', 'noreply@quickshul.com'),
        'name'          => env('PLATFORM_GMAIL_NAME', 'QuickShul'),
    ],

    'google' => [
        'client_id'      => env('GOOGLE_CLIENT_ID'),
        'client_secret'  => env('GOOGLE_CLIENT_SECRET'),
        'redirect'       => env('GOOGLE_REDIRECT_URI'),
        'gmail_redirect' => env('GMAIL_REDIRECT_URI', 'https://quickshul.com/auth/gmail/callback'),
    ],

    'quickbooks' => [
        'client_id'          => env('QB_CLIENT_ID'),
        'client_secret'      => env('QB_CLIENT_SECRET'),
        'environment'        => env('QB_ENVIRONMENT', 'production'),
        'redirect_uri'       => env('QB_REDIRECT_URI'),
        'realm_id'           => env('QB_REALM_ID'),
        'donation_item_id'   => env('QB_DONATION_ITEM_ID'),
    ],

];
