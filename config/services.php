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
    /*
    |--------------------------------------------------------------------------
    | Fonnte WhatsApp Gateway
    |--------------------------------------------------------------------------
    | Token didapat dari https://fonnte.com setelah daftar & tambah device.
    | admin_number: nomor WA admin yang menerima notif setiap transaksi.
    |               Kosongkan ('') jika tidak ingin notif ke admin.
    */
    'fonnte' => [
        'token'        => env('FONNTE_TOKEN', ''),
        'admin_number' => env('FONNTE_ADMIN_NUMBER', ''),
    ],

];
