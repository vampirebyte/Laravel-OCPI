<?php

namespace Ocpi\Modules\Cpo\Payments\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A PTP told us what it captured on the card for a session (POST
 * financial-advice-confirmations, or pulled with GET).
 */
class FinancialAdviceConfirmationReceived implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $financialAdviceConfirmationId,
    ) {}
}
