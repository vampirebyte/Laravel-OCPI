<?php

namespace Ocpi\Modules\Cpo\Locations\Events;

use Ocpi\Models\Party;
use Ocpi\Support\Client\Client;

class PushLocation
{
    /**
     * $payload is a Location in the shape of the receiving party's version
     * (see Ocpi\Models\Cpo\Dto\Location). Only from OCPI 2.2 does it carry
     * country_code / party_id - our own, filled in when missing.
     */
    public function push(array $payload, string $receiverPartyId, string $locationId): mixed
    {
        $party = Party::where('code', $receiverPartyId)->firstOrFail();

        $defaults = ['last_updated' => now()->toIso8601ZuluString()];
        if (version_compare((string) $party->version, '2.2', '>=')) {
            $defaults['party_id'] = config('ocpi-cpo.party.party_id');
            $defaults['country_code'] = config('ocpi-cpo.party.country_code');
        }

        $payload = array_merge($defaults, $payload);

        return (new Client($party, 'locations'))
            ->cpoLocations()
            ->push(
                payload: $payload,
                countryCode: config('ocpi-cpo.party.country_code'),
                partyId: config('ocpi-cpo.party.party_id'),
                locationId: $locationId,
            );
    }

    public function pushEvse(array $payload, string $receiverPartyId, string $locationId, string $evseUid): mixed
    {
        $party = Party::where('code', $receiverPartyId)->firstOrFail();

        $payload = array_merge([
            'last_updated' => now()->toIso8601ZuluString(),
        ], $payload);

        return (new Client($party, 'locations'))
            ->cpoLocations()
            ->pushEvse(
                payload: $payload,
                countryCode: config('ocpi-cpo.party.country_code'),
                partyId: config('ocpi-cpo.party.party_id'),
                locationId: $locationId,
                evseUid: $evseUid,
            );
    }

    public function pushConnector(array $payload, string $receiverPartyId, string $locationId, string $evseUid, string $connectorId): mixed
    {
        $party = Party::where('code', $receiverPartyId)->firstOrFail();

        $payload = array_merge([
            'last_updated' => now()->toIso8601ZuluString(),
        ], $payload);

        return (new Client($party, 'locations'))
            ->cpoLocations()
            ->pushConnector(
                payload: $payload,
                countryCode: config('ocpi-cpo.party.country_code'),
                partyId: config('ocpi-cpo.party.party_id'),
                locationId: $locationId,
                evseUid: $evseUid,
                connectorId: $connectorId,
            );
    }
}
