<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Party
    |--------------------------------------------------------------------------
    */

    'party' => [
        'party_id' => env('OCPI_CPO_PARTY_ID'),
        'country_code' => env('OCPI_CPO_COUNTRY_CODE'),
        'business_details' => [
            'name' => env('OCPI_CPO_NAME', env('APP_NAME')),
            'website' => env('OCPI_CPO_WEBSITE', env('APP_URL')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Versions
    |--------------------------------------------------------------------------
    */

    'versions' => [
        '2.1.1' => [
            'base_url' => env('OCPI_CPO_V2_1_1_BASE_URL', env('APP_URL').'/ocpi/2.1.1'),
            'modules' => [
                'credentials',
                'locations',
                'sessions',
                'cdrs',
                'tokens',
                'commands',
                'tariffs',
            ],
        ],

        '2.2.1' => [
            'base_url' => env('OCPI_CPO_V2_2_1_BASE_URL', env('APP_URL').'/ocpi/2.2.1'),
            'modules' => [
                'credentials',
                'locations',
                'sessions',
                'cdrs',
                'tokens',
                'commands',
                'tariffs',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Modules
    |--------------------------------------------------------------------------
    */

    // 'module' => [
    //     'cdrs' => [
    //         'id_separator' => env('OCPI_CPO_MODULE_CDRS_ID_SEPARATOR', '___'),
    //     ],
    // ],

];
