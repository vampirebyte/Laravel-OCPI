<?php

namespace Ocpi\Modules\Cpo\Payments\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A PTP sent us a terminal (POST terminals, or pulled with GET terminals).
 * $created: the PTP created it just now - new, or for our activation request.
 */
class TerminalReceived implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $paymentTerminalId,
        public bool $created,
    ) {}
}
