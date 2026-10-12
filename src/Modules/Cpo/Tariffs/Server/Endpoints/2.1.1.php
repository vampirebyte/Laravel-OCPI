<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Tariffs\Server\Controllers\TariffsController;

Route::get('tariffs', [TariffsController::class, 'index'])->name('ocpi-cpo.tariffs.index');
