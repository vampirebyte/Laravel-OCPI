<?php

namespace Ocpi\Modules\Cpo\Sessions\Events;

use Ocpi\Models\Party;
use Ocpi\Support\Client\Client;

/**
 * Sends a session to the eMSP it is charged to: PUT the whole session (see
 * Ocpi\Models\Cpo\Dto\Session, in the eMSP's version), PATCH what changed.
 * The URL is {country_code}/{party_id}/{session_id} with OUR country code
 * and party id, the CPO owning the session.
 */
class PushSession
{
    public function push(array $payload, string $receiverPartyId, string $sessionId): mixed
    {
        $party = Party::where('code', $receiverPartyId)->firstOrFail();

        $defaults = ['last_updated' => now()->toIso8601String()];
        if (version_compare((string) $party->version, '2.2', '>=')) {
            $defaults['country_code'] = config('ocpi-cpo.party.country_code');
            $defaults['party_id'] = config('ocpi-cpo.party.party_id');
        }

        return (new Client($party, 'sessions'))
            ->cpoSessions()
            ->push(
                payload: array_merge($defaults, $payload),
                countryCode: config('ocpi-cpo.party.country_code'),
                partyId: config('ocpi-cpo.party.party_id'),
                sessionId: $sessionId,
            );
    }

    /**
     * Only the changed fields (e.g. kwh, charging_periods), last_updated included.
     */
    public function patch(array $fields, string $receiverPartyId, string $sessionId): mixed
    {
        $party = Party::where('code', $receiverPartyId)->firstOrFail();

        return (new Client($party, 'sessions'))
            ->cpoSessions()
            ->patch(
                payload: array_merge(['last_updated' => now()->toIso8601String()], $fields),
                countryCode: config('ocpi-cpo.party.country_code'),
                partyId: config('ocpi-cpo.party.party_id'),
                sessionId: $sessionId,
            );
    }
}
