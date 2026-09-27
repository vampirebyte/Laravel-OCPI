<?php

namespace Ocpi\Modules\Cpo\Tariffs\Events;

use Ocpi\Models\Party;
use Ocpi\Support\Client\Client;

class PushTariff
{
    public function push(array $payload, string $receiverPartyId, string $tariffId): mixed
    {
        $party = Party::where('code', $receiverPartyId)->firstOrFail();

        $payload = array_merge([
            'party_id' => config('ocpi-cpo.party.party_id'),
            'country_code' => config('ocpi-cpo.party.country_code'),
            'last_updated' => now()->toIso8601String(),
        ], $payload);

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
