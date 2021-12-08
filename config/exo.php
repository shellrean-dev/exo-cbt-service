<?php

return [
    /*
    | Current Extraordinary CBT version
    | @name ristretto
    | @code 3.0.0
    |
    */
    'version' => [
        'name' => env('EXO_APP_VERSION_NAME', 'ristretto'),
        'code' => env('EXO_APP_VERSION_CODE', '3.0.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi ujian default
    |--------------------------------------------------------------------------
    |
    | Pengaturan ini mengatur tentang bagaimana cara ujian berlangsung dan
    | bagaimana cara memanage nya dengan rapih dan terstruktur
    |
    */
    'token_expired' => env('EXO_TOKEN_EXAM_EXPIRES', 600),

    'log' => env('EXO_ALLOW_LOG_ERROR', true),

    'enable_cache' => env('EXO_ENABLE_CACHING', false),

    'enable_socket' => env('EXO_ENABLE_SOCKET', false),

    'softdel' => env('EXO_ENABLE_SOFTDEL', true),

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi oauth
    |--------------------------------------------------------------------------
    |
    | Pengaturan ini mengatur tentang bagaimana cara login ke sistem
    | melalui metode oauth
    |
    */
    'oauth_server_url' => env('EXO_OAUTH_SERVER_URL', 'http://localhost'),
];
