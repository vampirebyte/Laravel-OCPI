<?php

use Illuminate\Support\Facades\Route;
use Ocpi\Modules\Cpo\Payments\Server\Controllers\FinancialAdviceConfirmationsController;
use Ocpi\Modules\Cpo\Payments\Server\Controllers\TerminalsController;
use Ocpi\Support\Server\Middlewares\Cpo\EnsurePartyRole;

// The payments module (DirectPayment extension of 2.2.1) - for PTPs only.
Route::middleware(EnsurePartyRole::class.':PTP')
    ->prefix('payments')
    ->group(function () {
        Route::get('terminals', [TerminalsController::class, 'index'])->name('ocpi-cpo.2_2_1.payments.terminals.index');
        Route::post('terminals', [TerminalsController::class, 'store'])->name('ocpi-cpo.2_2_1.payments.terminals.store');
        Route::get('terminals/{terminalId}', [TerminalsController::class, 'show'])->name('ocpi-cpo.2_2_1.payments.terminals.show');

        Route::get('financial-advice-confirmations', [FinancialAdviceConfirmationsController::class, 'index'])->name('ocpi-cpo.2_2_1.payments.financial-advice-confirmations.index');
        Route::post('financial-advice-confirmations', [FinancialAdviceConfirmationsController::class, 'store'])->name('ocpi-cpo.2_2_1.payments.financial-advice-confirmations.store');
        Route::get('financial-advice-confirmations/{confirmationId}', [FinancialAdviceConfirmationsController::class, 'show'])->name('ocpi-cpo.2_2_1.payments.financial-advice-confirmations.show');
    });
