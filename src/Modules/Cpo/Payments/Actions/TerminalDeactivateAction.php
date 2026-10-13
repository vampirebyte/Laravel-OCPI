<?php

namespace Ocpi\Modules\Cpo\Payments\Actions;

use Ocpi\Models\Payments\PaymentTerminal;
use Ocpi\Support\Client\Client;

class TerminalDeactivateAction
{
    /**
     * Deactivate a terminal at the PTP, e.g. broken or moved elsewhere. An
     * activation request the PTP did not answer yet is only withdrawn here.
     */
    public function handle(PaymentTerminal $terminal): PaymentTerminal
    {
        if ($terminal->terminal_id !== null) {
            (new Client($terminal->party, 'payments'))->cpoPayments()->deactivate($terminal->terminal_id);
        }

        $terminal->update(['deactivated_at' => now()]);

        return $terminal;
    }
}
