<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\CPO\Versions\Server\Controllers\VersionsController;
use Ocpi\Modules\CPO\Versions\Server\Controllers\VersionDetailsController;
use Ocpi\Support\CPO\Server\Middlewares\IdentifyParty;
use Ocpi\Support\Server\Middlewares\LogRequest;


Route::middleware(['api', LogRequest::class, IdentifyParty::class])
    ->prefix('ocpi/cpo')
    ->group(function () {
        Route::get('/versions', VersionsController::class);
        Route::get('/{version}', VersionDetailsController::class);
    });