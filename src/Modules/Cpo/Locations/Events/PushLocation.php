<?php

namespace Ocpi\Modules\Cpo\Locations\Events;

use Ocpi\Models\Party;
use Ocpi\Support\Client\Client;

class PushLocation
{
    public function push(array $payload, string $receiverPartyId, string $locationId): mixed
    {
        $party = Party::where('code', $receiverPartyId)->firstOrFail();

        $payload = array_merge([
            'party_id' => config('ocpi-cpo.party.party_id'),
            'country_code' => config('ocpi-cpo.party.country_code'),
            'last_updated' => now()->toIso8601String(),
        ], $payload);

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
            'last_updated' => now()->toIso8601String(),
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
            'last_updated' => now()->toIso8601String(),
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
