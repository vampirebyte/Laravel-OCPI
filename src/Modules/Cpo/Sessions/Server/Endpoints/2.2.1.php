<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Sessions\Server\Controllers\SessionsController;

Route::get('sessions', [SessionsController::class, 'index'])->name('ocpi-cpo.2_2_1.sessions.index');
