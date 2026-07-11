<?php

namespace Ocpi\Modules\Cpo\Sessions\Events;

use Ocpi\Models\Party;
use Ocpi\Support\Client\Client;

class PushSession
{
    public function push(array $payload, string $receiverPartyId, string $sessionId): mixed
    {
        $party = Party::where('code', $receiverPartyId)->firstOrFail();
        $partyRole = $party->roles()->firstOrFail();

        $payload = array_merge([
            'party_id' => config('ocpi-cpo.party.party_id'),
            'last_updated' => now()->toIso8601String(),
        ], $payload);

        return (new Client($party, 'sessions'))
            ->cpoSessions()
            ->push(
                payload: $payload,
                countryCode: $partyRole->country_code,
                partyId: config('ocpi-cpo.party.party_id'),
                sessionId: $sessionId,
            );
    }
}
