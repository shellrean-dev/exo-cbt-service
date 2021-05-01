<?php

return [
    /*
    | Current Extraordinary CBT version
    | @name ristretto
    | @code 3.0.0
    |
    */
    'version' => [
        'name' => 'ristretto',
        'code' => '3.0.0'
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
];