<?php

namespace Ocpi\Modules\Cpo\Cdrs\Events;

use Ocpi\Models\Party;
use Ocpi\Support\Client\Client;

class PushCdr
{
    /**
     * $payload is a CDR in the shape of the receiving party's version (see
     * Ocpi\Models\Cpo\Dto\Cdr). Only from OCPI 2.2 does it carry country_code
     * / party_id - our own, filled in when missing.
     */
    public function push(array $payload, string $receiverPartyId): mixed
    {
        $party = Party::where('code', $receiverPartyId)->firstOrFail();

        $defaults = ['last_updated' => now()->toIso8601String()];
        if (version_compare((string) $party->version, '2.2', '>=')) {
            $defaults['party_id'] = config('ocpi-cpo.party.party_id');
            $defaults['country_code'] = config('ocpi-cpo.party.country_code');
        }

        $payload = array_merge($defaults, $payload);

        return (new Client($party, 'cdrs'))
            ->cpoCdrs()
            ->push($payload);
    }
}
