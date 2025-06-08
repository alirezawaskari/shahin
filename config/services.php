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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'sms' => [
        'apikey' => 'cpCIuUbmtBhYFw41+kpYs0mSNw+mkN4aZBELwgUMN+A',
        'linenumber' => '10002622'
    ],

    'neginSms' => [
        'portalcode' => '10002239',
        'username' => 'fr50568',
        'password' => '12121364',
    ],

    'emta' => [
        'client_id' => 'E4A4D3E4-B5AC-498C-BAD3-59BD955431E5',
        'secret_id' => 'C9DD48373FB440A5A9D0875F720A3B20',
        'url' => 'https://api.ecsw.ir'
    ],

    'mellat' => [
        'terminal' => env('MELLAT_TERMINAL'),
        'username' => env('MELLAT_USERNAME'),
        'password' => env('MELLAT_PASSWORD'),
    ],
    'saman' => [
        'id' => env('SAMAN_MERCHANT_ID'),
    ],
    'zarinpal' => [
        'id' => env('ZARIN_ID'),
        'url' => 'https://www.zarinpal.com',//if test => convert www to the sandbox
    ],
];
