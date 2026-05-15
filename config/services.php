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

    'jitsi' => [
        'domain' => env('JITSI_DOMAIN', '8x8.vc'),
        'app_id' => env('JITSI_APP_ID'),
        'kid' => env('JITSI_KID'),
        'private_key_path' => env('JITSI_PRIVATE_KEY_PATH', 'storage/app/private/jaas-private-key.pk'),
    ],

    'piston' => [
        'url' => env('PISTON_URL', 'https://emkc.org/api/v2/piston'),
        'timeout' => (int) env('PISTON_TIMEOUT', 10),
    ],

];
