<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\CPO\Versions\Server\Controllers\VersionsController;
use Ocpi\Modules\CPO\Versions\Server\Controllers\VersionDetailsController;

Route::prefix('ocpi/cpo')->group(function () {
    Route::get('/versions', VersionsController::class);
    Route::get('/{version}', VersionDetailsController::class);
});
