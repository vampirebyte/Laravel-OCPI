<?php

namespace Ocpi\Modules\Cpo\Cdrs\Events;

use Ocpi\Models\Party;
use Ocpi\Support\Client\Client;

class PushCdr
{
    public function push(array $payload, string $receiverPartyId): mixed
    {
        $party = Party::where('code', $receiverPartyId)->firstOrFail();

        $payload = array_merge([
            'party_id' => config('ocpi-cpo.party.party_id'),
            'country_code' => config('ocpi-cpo.party.country_code'),
            'last_updated' => now()->toIso8601String(),
        ], $payload);

        return (new Client($party, 'cdrs'))
            ->cpoCdrs()
            ->push($payload);
    }
}
