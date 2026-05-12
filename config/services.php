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

    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
    ],

    'appointments' => [
        'fee' => env('APPOINTMENT_FEE', 500),
    ],

    'webrtc' => [
        'ice_servers' => array_values(array_filter([
            ['urls' => env('WEBRTC_STUN_URL', 'stun:stun.l.google.com:19302')],
            env('WEBRTC_TURN_URL') ? [
                'urls' => env('WEBRTC_TURN_URL'),
                'username' => env('WEBRTC_TURN_USERNAME'),
                'credential' => env('WEBRTC_TURN_CREDENTIAL'),
            ] : null,
        ])),
    ],

];
