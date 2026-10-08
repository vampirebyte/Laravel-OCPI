<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Locations\Server\Controllers\LocationsController;

Route::get('locations', [LocationsController::class, 'index'])->name('ocpi-cpo.2_2_1.locations.cpo-index');
Route::get('locations/{locationId}', [LocationsController::class, 'show'])->name('ocpi-cpo.2_2_1.locations.cpo-show');
Route::get('locations/{locationId}/{evseUid}', [LocationsController::class, 'showEvse'])->name('ocpi-cpo.2_2_1.locations.cpo-show-evse');
Route::get('locations/{locationId}/{evseUid}/{connectorId}', [LocationsController::class, 'showConnector'])->name('ocpi-cpo.2_2_1.locations.cpo-show-connector');
