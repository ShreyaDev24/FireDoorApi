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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'google' => [
        'client_id' => '776676140942-3qcffsf5tcl2hjomtcnnpa3k93hr2bii.apps.googleusercontent.com',
        'client_secret' => '-0q4izX_eTyvF9pUvu3eqo26',
        'redirect' => env('APP_URL').'callback/google',
    ], 
    'facebook' => [
        'client_id' => '969989573608098',
        'client_secret' => 'f589842c3edbec499430135fb35f3a55',
        'redirect' => env('APP_URL').'callback/facebook',
    ],
    // 'facebook' => [
    //     'client_id' => env('FACEBOOK_CLIENT_ID'),
    //     'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    //     'redirect' => env('FACEBOOK_CALLBACK_URL'),
    // ],

];
