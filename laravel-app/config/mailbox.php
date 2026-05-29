<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active Email Provider
    |--------------------------------------------------------------------------
    |
    | Change MAIL_PROVIDER to "outlook" or "gmail" to switch providers without
    | changing routes or controller code.
    |
    */

    'provider' => env('MAIL_PROVIDER', 'auto'),
    'timeout' => (int) env('MAIL_PROVIDER_TIMEOUT', 15),

    'outlook' => [
        'tenant_id' => env('OUTLOOK_TENANT_ID'),
        'client_id' => env('OUTLOOK_CLIENT_ID'),
        'client_secret' => env('OUTLOOK_CLIENT_SECRET'),
        'mailbox' => env('OUTLOOK_MAILBOX'),
        'token_url' => env('OUTLOOK_TOKEN_URL', 'https://login.microsoftonline.com'),
        'graph_url' => env('OUTLOOK_GRAPH_URL', 'https://graph.microsoft.com/v1.0'),
    ],

    'gmail' => [
        'client_id' => env('GMAIL_CLIENT_ID'),
        'client_secret' => env('GMAIL_CLIENT_SECRET'),
        'refresh_token' => env('GMAIL_REFRESH_TOKEN'),
        'access_token' => env('GMAIL_ACCESS_TOKEN'),
        'user_id' => env('GMAIL_USER_ID', 'me'),
        'token_url' => env('GMAIL_TOKEN_URL', 'https://oauth2.googleapis.com/token'),
        'api_url' => env('GMAIL_API_URL', 'https://gmail.googleapis.com/gmail/v1'),
    ],
];
