<?php

namespace Ocpi\Modules\Cpo\Payments\Actions;

use InvalidArgumentException;
use Ocpi\Models\Payments\PaymentTerminal;
use Ocpi\Support\Client\Client;

class TerminalAssignAction
{
    /**
     * Assign locations / EVSEs to a terminal the PTP created (PATCH): the
     * terminal offers the EVSEs of the locations plus the EVSEs listed.
     *
     * @param  array<int, string>  $locationIds
     * @param  array<int, string>  $evseUids
     */
    public function handle(PaymentTerminal $terminal, array $locationIds, array $evseUids): PaymentTerminal
    {
        if ($terminal->terminal_id === null) {
            throw new InvalidArgumentException('The PTP has not created this terminal yet.');
        }

        $fields = [
            'location_ids' => array_values($locationIds),
            'evse_uids' => array_values($evseUids),
            'last_updated' => now()->toIso8601ZuluString(),
        ];

        (new Client($terminal->party, 'payments'))->cpoPayments()->patch($terminal->terminal_id, $fields);

        $terminal->update([
            'object' => array_merge($terminal->object, $fields),
            'last_updated' => $fields['last_updated'],
        ]);

        return $terminal;
    }
}
