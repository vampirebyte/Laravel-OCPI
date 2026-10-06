<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Emsp\Tariffs\Server\Controllers\DeleteController;
use Ocpi\Modules\Emsp\Tariffs\Server\Controllers\GetController;
use Ocpi\Modules\Emsp\Tariffs\Server\Controllers\PatchController;
use Ocpi\Modules\Emsp\Tariffs\Server\Controllers\PutController;
use Ocpi\Support\Server\Middlewares\IdentifyPartyRole;

Route::middleware([
    IdentifyPartyRole::class,
])
    ->prefix('tariffs')
    ->name('tariffs')
    ->group(function () {
        Route::get('{country_code?}/{party_id?}/{tariff_id?}', GetController::class);
        Route::put('{country_code}/{party_id}/{tariff_id}', PutController::class)->name('.put');
        Route::patch('{country_code}/{party_id}/{tariff_id}', PatchController::class)->name('.patch');
        Route::delete('{country_code}/{party_id}/{tariff_id}', DeleteController::class)->name('.delete');
    });
