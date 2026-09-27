<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Commands\Server\Controllers\CommandsController;

Route::post('commands/{commandType}', [CommandsController::class, 'handle'])->name('ocpi-cpo.commands.handle');
