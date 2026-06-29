<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Locations\Server\Controllers\LocationsController;
use Ocpi\Support\Server\Middlewares\Cpo\IdentifyParty;

Route::prefix('ocpi/cpo/2.1.1')
    ->middleware([IdentifyParty::class])
    ->group(function () {
        Route::get('locations', [LocationsController::class, 'index'])->name('ocpi-cpo.locations.cpo-index');
        Route::get('locations/{locationId}', [LocationsController::class, 'show'])->name('ocpi-cpo.locations.cpo-show');
    });
