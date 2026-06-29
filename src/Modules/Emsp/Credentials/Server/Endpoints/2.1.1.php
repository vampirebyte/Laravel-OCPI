<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Emsp\Credentials\Server\Controllers\DeleteController;
use Ocpi\Modules\Emsp\Credentials\Server\Controllers\GetController;
use Ocpi\Modules\Emsp\Credentials\Server\Controllers\V2_1_1\PostController;
use Ocpi\Modules\Emsp\Credentials\Server\Controllers\V2_1_1\PutController;

Route::prefix('credentials')
    ->name('credentials')
    ->group(function () {
        Route::get('/', GetController::class);
        Route::post('/', PostController::class)->name('.post');
        Route::put('/', PutController::class)->name('.put');
        Route::delete('/', DeleteController::class)->name('.delete');
    });
