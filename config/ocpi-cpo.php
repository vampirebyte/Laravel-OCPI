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
    | Commands
    |--------------------------------------------------------------------------
    |
    | OCPI 2.2.1: seconds the eMSP should wait for the asynchronous result of a
    | command, sent as `timeout` in the CommandResponse.
    */

    'commands' => [
        'timeout' => (int) env('OCPI_CPO_COMMANDS_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | CDRs
    |--------------------------------------------------------------------------
    |
    | ocpi:cdrs:cpo_retry_unsent sends again the CDRs an eMSP has not accepted:
    | the ones older than retry_after_minutes, for retry_for_days.
    */

    'cdrs' => [
        'retry_after_minutes' => (int) env('OCPI_CPO_CDRS_RETRY_AFTER_MINUTES', 5),
        'retry_for_days' => (int) env('OCPI_CPO_CDRS_RETRY_FOR_DAYS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Most objects a GET list endpoint returns per page - a larger `limit` is cut down.
    */

    'pagination' => [
        'max_limit' => (int) env('OCPI_CPO_PAGINATION_MAX_LIMIT', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Versions
    |--------------------------------------------------------------------------
    */

    'versions' => [
        '2.1.1' => [
            'base_url' => env('OCPI_CPO_V2_1_1_BASE_URL', env('APP_URL') . '/ocpi/cpo/2.1.1'),
            'modules' => [
                'credentials' => ['role' => 'BOTH'],
                'locations'   => ['role' => 'SENDER'],
                'tariffs'     => ['role' => 'SENDER'],
                'sessions'    => ['role' => 'SENDER'],
                'cdrs'        => ['role' => 'SENDER'],
                'tokens'      => ['role' => 'RECEIVER'],
                'commands'    => ['role' => 'RECEIVER'],
            ],
        ],

        '2.2.1' => [
            'base_url' => env('OCPI_CPO_V2_2_1_BASE_URL', env('APP_URL') . '/ocpi/cpo/2.2.1'),
            'modules' => [
                'credentials' => ['role' => 'RECEIVER'],
                'locations'   => ['role' => 'SENDER'],
                'tariffs'     => ['role' => 'SENDER'],
                'sessions'    => ['role' => 'SENDER'],
                'cdrs'        => ['role' => 'SENDER'],
                'tokens'      => ['role' => 'RECEIVER'],
                'commands'    => ['role' => 'RECEIVER'],
                // DirectPayment extension: PTPs (payment terminal providers) push terminals and financial advice confirmations
                'payments'    => ['role' => 'RECEIVER'],
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
