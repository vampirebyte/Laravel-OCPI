<?php

namespace Ocpi\Modules\Cpo\Tariffs\Events;

use Ocpi\Models\Party;
use Ocpi\Support\Client\Client;

class DeleteTariff
{
    public function delete(string $receiverPartyId, string $tariffId): mixed
    {
        $party = Party::where('code', $receiverPartyId)->firstOrFail();

        return (new Client($party, 'tariffs'))
            ->cpoTariffs()
            ->remove(
                countryCode: config('ocpi-cpo.party.country_code'),
                partyId: config('ocpi-cpo.party.party_id'),
                tariffId: $tariffId,
            );
    }
}
