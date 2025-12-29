<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Asterisk Connection Settings
    |--------------------------------------------------------------------------
    |
    | Here you may specify the connection settings for your Asterisk instance.
    |
    */

    'host' => env('ARI_HOST', 'localhost'),

    'port' => (int) env('ARI_PORT', 8088),

    'user' => env('ARI_USER', 'asterisk'),

    'password' => env('ARI_PASSWORD', 'asterisk'),

    'root_uri' => env('ARI_ROOT_URI', '/ari'),

    'wss_enabled' => (bool) env('ARI_WSS_ENABLED', false),

    'subscribe_all' => (bool) env('ARI_SUBSCRIBE_ALL', false),
];
