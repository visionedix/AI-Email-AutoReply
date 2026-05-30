<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Driver
    |--------------------------------------------------------------------------
    |
    | Use "openai" for ChatGPT/OpenAI-backed generation, or "rule_based" to
    | keep the existing deterministic keyword/template behavior.
    |
    */

    'driver' => env('AI_DRIVER', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('AI_TIMEOUT', 30),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'chat-latest'),
    ],

];
