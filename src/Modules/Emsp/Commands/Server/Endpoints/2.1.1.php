<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Emsp\Commands\Server\Controllers\V2_1_1\GetController;
use Ocpi\Modules\Emsp\Commands\Server\Controllers\V2_1_1\PostController;

Route::prefix('commands')
    ->name('commands')
    ->group(function () {
        Route::post('{type}/{id?}', PostController::class)->name('.post');
        // Route only used in Versions details to give an endpoint for this Module to the CPO.
        Route::get('/', GetController::class);
    });
