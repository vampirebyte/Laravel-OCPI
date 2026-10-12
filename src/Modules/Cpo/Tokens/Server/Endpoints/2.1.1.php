<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Tokens\Server\Controllers\TokensController;

Route::get('tokens/{countryCode}/{partyId}/{tokenUid}', [TokensController::class, 'show'])->name('ocpi-cpo.tokens.show');
Route::put('tokens/{countryCode}/{partyId}/{tokenUid}', [TokensController::class, 'put'])->name('ocpi-cpo.tokens.put');
Route::patch('tokens/{countryCode}/{partyId}/{tokenUid}', [TokensController::class, 'patch'])->name('ocpi-cpo.tokens.patch');
