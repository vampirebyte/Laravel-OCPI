<?php

namespace Ocpi\Modules\Cpo\Commands\Events;

use Illuminate\Support\Facades\Http;
use Ocpi\Models\Party;

class PushCommandResult
{
    public function push(string $responseUrl, string $partyCode, string $result): void
    {
        $party = Party::where('code', $partyCode)->firstOrFail();

        Http::withHeaders([
            'Authorization' => 'Token ' . $party->encoded_server_token,
            'Content-Type' => 'application/json',
        ])->post($responseUrl, ['result' => $result]);
    }
}
