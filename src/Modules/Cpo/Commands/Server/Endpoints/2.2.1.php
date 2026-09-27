<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Commands\Server\Controllers\CommandsController;

Route::post('commands/{commandType}', [CommandsController::class, 'handle'])->name('ocpi-cpo.2_2_1.commands.handle');
