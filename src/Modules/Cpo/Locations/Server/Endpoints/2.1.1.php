<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Locations\Server\Controllers\LocationsController;

Route::get('locations', [LocationsController::class, 'index'])->name('ocpi-cpo.locations.cpo-index');
Route::get('locations/{locationId}', [LocationsController::class, 'show'])->name('ocpi-cpo.locations.cpo-show');
