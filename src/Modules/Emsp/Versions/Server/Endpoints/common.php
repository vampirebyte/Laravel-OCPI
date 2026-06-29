<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Emsp\Versions\Server\Controllers\InformationController;

Route::name('versions.')
    ->group(function () {
        Route::get('versions', InformationController::class)
            ->name('information');
    });
