<?php

namespace Ocpi\Modules\Cpo\Payments\Actions;

use Illuminate\Support\Facades\Validator;
use Ocpi\Models\Party;
use Ocpi\Models\Payments\PaymentTerminal;
use Ocpi\Modules\Cpo\Payments\Validators\TerminalValidator;
use Ocpi\Support\Client\Client;
use Throwable;

class TerminalActivateAction
{
    public function __construct(
        private readonly TerminalStoreAction $terminalStore,
    ) {}

    /**
     * Ask the PTP to create the terminal with this serial number (its TID) for
     * our locations / EVSEs. The PTP answers by creating it on our side (POST
     * terminals), matched on the serial number to the activation request
     * stored here - stored before asking, as the PTP may create it before it
     * answers us. Asking again for the same serial number replaces the request.
     *
     * @param  array<int, string>  $locationIds
     * @param  array<int, string>  $evseUids
     * @param  array<string, mixed>  $fields  more Terminal fields, e.g. invoice_creator, invoice_base_url
     */
    public function handle(Party $party, string $serialNumber, array $locationIds, array $evseUids, array $fields = []): PaymentTerminal
    {
        $object = array_merge($fields, [
            'country_code' => config('ocpi-cpo.party.country_code'),
            'party_id' => config('ocpi-cpo.party.party_id'),
            'serial_number' => $serialNumber,
            'location_ids' => array_values($locationIds),
            'evse_uids' => array_values($evseUids),
            'last_updated' => now()->toIso8601ZuluString(),
        ]);

        $terminal = $this->awaitingActivation($party, $serialNumber)
            ?? new PaymentTerminal(['party_id' => $party->id]);

        $terminal->fill([
            'serial_number' => $serialNumber,
            'object' => $object,
            'last_updated' => $object['last_updated'],
            'activation_requested_at' => now(),
        ]);
        $terminal->save();

        try {
            $response = (new Client($party, 'payments'))->cpoPayments()->activate($object);
        } catch (Throwable $exception) {
            // not asked after all - unless the PTP created it meanwhile
            if ($terminal->refresh()->terminal_id === null) {
                $terminal->delete();
            }

            throw $exception;
        }

        // a PTP may answer with the terminal it created
        if (is_array($response) && Validator::make($response, TerminalValidator::rules())->passes()) {
            return $this->terminalStore->handle($party, $response);
        }

        return $terminal->refresh();
    }

    private function awaitingActivation(Party $party, string $serialNumber): ?PaymentTerminal
    {
        return PaymentTerminal::query()
            ->where('party_id', $party->id)
            ->whereNull('terminal_id')
            ->whereNull('deactivated_at')
            ->where('serial_number', $serialNumber)
            ->latest('id')
            ->first();
    }
}
