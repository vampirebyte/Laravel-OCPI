<?php

namespace Ocpi\Modules\Cpo\Payments\Actions;

use Illuminate\Support\Carbon;
use Ocpi\Models\Party;
use Ocpi\Models\Payments\FinancialAdviceConfirmation;
use Ocpi\Modules\Cpo\Payments\Events\FinancialAdviceConfirmationReceived;

class FinancialAdviceConfirmationStoreAction
{
    /**
     * Store a FinancialAdviceConfirmation object a PTP sent us - the same id
     * again replaces it.
     *
     * @param  array<string, mixed>  $object  a valid FinancialAdviceConfirmation object (see FinancialAdviceConfirmationValidator)
     */
    public function handle(Party $party, array $object): FinancialAdviceConfirmation
    {
        $totalCostInclVat = $object['total_costs']['incl_vat'] ?? null;

        $confirmation = FinancialAdviceConfirmation::updateOrCreate(
            [
                'party_id' => $party->id,
                'confirmation_id' => (string) $object['id'],
            ],
            [
                'authorization_reference' => (string) $object['authorization_reference'],
                'currency' => (string) $object['currency'],
                'total_cost_excl_vat' => (float) $object['total_costs']['excl_vat'],
                'total_cost_incl_vat' => $totalCostInclVat !== null ? (float) $totalCostInclVat : null,
                'capture_status_code' => (string) $object['capture_status_code'],
                'capture_status_message' => $object['capture_status_message'] ?? null,
                'eft_data' => $object['eft_data'] ?? null,
                'last_updated' => Carbon::parse($object['last_updated']),
            ],
        );

        FinancialAdviceConfirmationReceived::dispatch($confirmation->id);

        return $confirmation;
    }
}
