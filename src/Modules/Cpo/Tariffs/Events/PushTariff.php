<?php

namespace Ocpi\Modules\Cpo\Tariffs\Events;

use Ocpi\Models\Party;
use Ocpi\Support\Client\Client;

class PushTariff
{
    /**
     * $payload is a Tariff in the shape of the receiving party's version (see
     * Tariff::toArray). Only from OCPI 2.2 does a tariff carry country_code /
     * party_id - our own, filled in when missing.
     */
    public function push(array $payload, string $receiverPartyId, string $tariffId): mixed
    {
        $party = Party::where('code', $receiverPartyId)->firstOrFail();

        $defaults = ['last_updated' => now()->toIso8601String()];
        if (version_compare((string) $party->version, '2.2', '>=')) {
            $defaults['party_id'] = config('ocpi-cpo.party.party_id');
            $defaults['country_code'] = config('ocpi-cpo.party.country_code');
        }

        $payload = array_merge($defaults, $payload);

        return (new Client($party, 'tariffs'))
            ->cpoTariffs()
            ->push(
                payload: $payload,
                countryCode: config('ocpi-cpo.party.country_code'),
                partyId: config('ocpi-cpo.party.party_id'),
                tariffId: $tariffId,
            );
    }
}
