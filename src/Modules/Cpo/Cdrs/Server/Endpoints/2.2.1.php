<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Cdrs\Server\Controllers\CdrsController;

Route::get('cdrs', [CdrsController::class, 'index'])->name('ocpi-cpo.2_2_1.cdrs.index');
