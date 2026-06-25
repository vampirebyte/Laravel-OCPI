<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server
    |--------------------------------------------------------------------------
    */

    'server' => [
        'enabled' => env('OCPI_SERVER_ENABLED', true),
        'routing' => [
            'uri_prefix' => env('OCPI_SERVER_ROUTING_URI_PREFIX', 'ocpi'),
            'name_prefix' => env('OCPI_SERVER_ROUTING_NAME_PREFIX', 'ocpi'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Client
    |--------------------------------------------------------------------------
    */

    'client' => [
        'server' => [
            'url' => env('OCPI_CLIENT_SERVER_URL', env('APP_URL')).'/'.env('OCPI_SERVER_ROUTING_URI_PREFIX', 'ocpi'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    */

    'database' => [
        'connection' => env('OCPI_DATABASE_CONNECTION', env('DB_CONNECTION', 'sqlite')),
        'table' => [
            'prefix' => env('OCPI_DATABASE_TABLE_PREFIX', 'ocpi_'),
        ],
    ],

];
