<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Tariffs\Server\Controllers\TariffsController;

Route::get('tariffs', [TariffsController::class, 'index'])->name('ocpi-cpo.2_2_1.tariffs.index');
