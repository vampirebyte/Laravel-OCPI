<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Support\Server\Middlewares\IdentifyParty;
use Ocpi\Support\Server\Middlewares\LogRequest;

Route::middleware([
    'api',
    LogRequest::class,
    IdentifyParty::class,
])
    ->prefix(config('ocpi.server.routing.uri_prefix'))
    ->name(config('ocpi.server.routing.name_prefix'))
    ->group(
        __DIR__.'/../../../Modules/Emsp/Versions/Server/Endpoints/common.php'
    );
