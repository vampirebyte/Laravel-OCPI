<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Emsp\Versions\Server\Controllers\DetailsController;

Route::name('versions.')
    ->group(function () {
        Route::get('/', DetailsController::class)
            ->name('details');
    });
