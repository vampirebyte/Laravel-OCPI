<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Commands\Server\Controllers\CommandsController;
use Ocpi\Support\Server\Middlewares\Cpo\IdentifyParty;

Route::prefix('ocpi/cpo/2.1.1')
    ->middleware([IdentifyParty::class])
    ->group(function () {
        Route::post('commands/{commandType}', [CommandsController::class, 'handle'])->name('ocpi-cpo.commands.handle');
    });
