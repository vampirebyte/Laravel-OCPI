<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Credentials\Server\Controllers\DeleteController;
use Ocpi\Modules\Cpo\Credentials\Server\Controllers\GetController;
use Ocpi\Modules\Cpo\Credentials\Server\Controllers\V2_1_1\PostController;
use Ocpi\Support\Server\Middlewares\IdentifyParty;
use Ocpi\Support\Server\Middlewares\LogRequest;

Route::middleware(['api', LogRequest::class, IdentifyParty::class])
    ->prefix('ocpi/cpo')
    ->name('credentials')
    ->group(function () {
        Route::get('/{version}/credentials', GetController::class);
        Route::post('/{version}/credentials', PostController::class)->name('.post');
        Route::delete('/{version}/credentials', DeleteController::class)->name('.delete');
    });
