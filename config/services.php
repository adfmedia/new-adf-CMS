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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'cloudflare' => [
        'email'     => env('CLOUDFLARE_EMAIL'),
        'api_key'   => env('CLOUDFLARE_API_KEY'),
        'domain'    => env('CLOUDFLARE_DOMAIN')
    ],

    'recaptcha' => [
        'site_key'      => env('RECAPTCHA_SITE_KEY'),
        'secret_key'    => env('RECAPTCHA_SECRET_KEY'),
        'min_score'     => env('RECAPTCHA_MIN_SCORE', '0.5'),
        'show_badge'    => env('RECAPTCHA_SHOW_BADGE', false)
    ],

    'bing_webmaster'    => [
        'index_now'         => [
            'api_key'           => env('BING_INDEXNOW_APIKEY'),
            'key_location'      => env('BING_INDEXNOW_KEYLOCATION'),
            'log_calls'         => env('BING_INDEXNOW_LOGCALLS', false)
        ]
    ],

    'tinify'   => [
        'api_key'   => env('TINIFY_API_KEY'),
        'enabled'   => env('TINIFY_ENABLED', true)
    ],

];
