<?php

namespace Ocpi\Modules\Cpo\Cdrs\Actions;

use Ocpi\Models\Cdrs\CpoCdr;
use Ocpi\Models\Cpo\Dto\Cdr;
use Ocpi\Models\Party;

class CdrStoreAction
{
    /**
     * Store a CDR for an eMSP in the shape of each OCPI version - what GET cdrs
     * serves and CdrSendAction sends. Storing the same CDR id again replaces it.
     *
     * @param  string|null  $reference  what the host application made it from, e.g. its session id
     */
    public function handle(Party $party, Cdr $cdr, ?string $reference = null): CpoCdr
    {
        return CpoCdr::updateOrCreate(
            ['party_id' => $party->id, 'cdr_id' => $cdr->id],
            [
                'reference' => $reference,
                'currency' => $cdr->currency,
                'total_cost_excl_vat' => $cdr->totalCost->exclVat,
                'total_cost_incl_vat' => $cdr->totalCost->inclVat,
                'object_2_1_1' => $cdr->toArray('2.1.1'),
                'object_2_2_1' => $cdr->toArray('2.2.1'),
            ],
        );
    }
}
