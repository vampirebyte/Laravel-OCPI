<?php

namespace Ocpi\Modules\Cpo\Payments\Actions;

use Illuminate\Support\Carbon;
use Ocpi\Models\Party;
use Ocpi\Models\Payments\PaymentTerminal;
use Ocpi\Modules\Cpo\Payments\Events\TerminalReceived;

class TerminalStoreAction
{
    /**
     * Store a Terminal object a PTP sent us. A terminal we do not know yet is
     * our activation request when the serial number matches. The fields the
     * PTP leaves out keep their value; when it creates the terminal of an
     * activation request without locations / EVSEs, the ones we activated it
     * for stay - TerminalReceived is the host application's cue to send them.
     *
     * @param  array<string, mixed>  $object  a valid Terminal object (see TerminalValidator)
     */
    public function handle(Party $party, array $object): PaymentTerminal
    {
        $terminal = $this->find($party, (string) $object['terminal_id'])
            ?? $this->awaitingActivation($party, $object['serial_number'] ?? null)
            ?? new PaymentTerminal(['party_id' => $party->id, 'object' => []]);

        $created = $terminal->terminal_id === null;
        $merged = array_merge($terminal->object, $object);

        if ($created && $terminal->exists && empty($object['location_ids']) && empty($object['evse_uids'])) {
            $merged['location_ids'] = $terminal->locationIds();
            $merged['evse_uids'] = $terminal->evseUids();
        }

        $terminal->fill([
            'terminal_id' => (string) $object['terminal_id'],
            'serial_number' => $merged['serial_number'] ?? null,
            'object' => $merged,
            'last_updated' => Carbon::parse($object['last_updated']),
        ]);
        $terminal->save();

        TerminalReceived::dispatch($terminal->id, $created);

        return $terminal;
    }

    private function find(Party $party, string $terminalId): ?PaymentTerminal
    {
        return PaymentTerminal::query()
            ->where('party_id', $party->id)
            ->where('terminal_id', $terminalId)
            ->first();
    }

    private function awaitingActivation(Party $party, ?string $serialNumber): ?PaymentTerminal
    {
        if ($serialNumber === null || $serialNumber === '') {
            return null;
        }

        return PaymentTerminal::query()
            ->where('party_id', $party->id)
            ->whereNull('terminal_id')
            ->whereNull('deactivated_at')
            ->where('serial_number', $serialNumber)
            ->latest('id')
            ->first();
    }
}
