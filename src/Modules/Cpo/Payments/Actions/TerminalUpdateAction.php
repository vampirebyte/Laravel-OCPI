<?php

namespace Ocpi\Modules\Cpo\Payments\Actions;

use InvalidArgumentException;
use Ocpi\Models\Payments\PaymentTerminal;
use Ocpi\Support\Client\Client;

class TerminalUpdateAction
{
    /**
     * Send the whole terminal to the PTP (PUT) with these fields changed,
     * e.g. invoice_creator and invoice_base_url.
     *
     * @param  array<string, mixed>  $fields
     */
    public function handle(PaymentTerminal $terminal, array $fields = []): PaymentTerminal
    {
        if ($terminal->terminal_id === null) {
            throw new InvalidArgumentException('The PTP has not created this terminal yet.');
        }

        $object = array_merge($terminal->toOcpiArray(), $fields, [
            'last_updated' => now()->toIso8601ZuluString(),
        ]);

        (new Client($terminal->party, 'payments'))->cpoPayments()->put($terminal->terminal_id, $object);

        $terminal->update([
            'object' => $object,
            'last_updated' => $object['last_updated'],
        ]);

        return $terminal;
    }
}
