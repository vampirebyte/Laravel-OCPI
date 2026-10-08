<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Credentials\Server\Controllers\DeleteController;
use Ocpi\Modules\Cpo\Credentials\Server\Controllers\GetController;
use Ocpi\Modules\Cpo\Credentials\Server\Controllers\PostController;
use Ocpi\Modules\Cpo\Credentials\Server\Controllers\PutController;
use Ocpi\Support\Server\Middlewares\Cpo\IdentifyParty;
use Ocpi\Support\Server\Middlewares\LogRequest;

Route::middleware(['api', LogRequest::class, IdentifyParty::class])
    ->prefix('ocpi/cpo')
    ->name('credentials')
    ->whereIn('version', array_keys(config('ocpi-cpo.versions', [])))
    ->group(function () {
        Route::get('/{version}/credentials', GetController::class);
        Route::post('/{version}/credentials', PostController::class)->name('.cpo-post');
        Route::put('/{version}/credentials', PutController::class)->name('.cpo-put');
        Route::delete('/{version}/credentials', DeleteController::class)->name('.cpo-delete');
    });
