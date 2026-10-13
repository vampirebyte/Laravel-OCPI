<?php

namespace Ocpi\Modules\Shared\Credentials\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ocpi\Models\Party;
use Ocpi\Support\Client\Client;
use Throwable;

class PartyDeleteAction
{
    /**
     * Ends the connection with a party - an EMSP of our CPO or a CPO of our
     * EMSP: its OCPI client token stops working and its roles are removed.
     * The party is soft deleted, so its sessions, CDRs, tokens, locations and
     * tariffs are kept for the history.
     *
     * When we end it, a registered party is told first - DELETE on its
     * credentials endpoint. It is removed even when that call fails, e.g.
     * when the party is down: it can no longer call us either way.
     *
     * @return bool false when the party should have been told but was not
     */
    public function handle(Party $party, bool $tellParty): bool
    {
        $partyWasTold = true;

        if ($tellParty && $party->registered && isset($party->endpoints['credentials'])) {
            try {
                (new Client($party, 'credentials'))->credentials()->delete();
            } catch (Throwable $exception) {
                $partyWasTold = false;
                Log::channel('ocpi')->error('OCPI credentials DELETE failed - the party is removed anyway', ['party' => $party->code, 'error' => $exception->getMessage()]);
            }
        }

        DB::connection(config('ocpi.database.connection'))
            ->transaction(function () use ($party) {
                $party->registered = false;
                $party->save();
                $party->delete();

                $party->roles()->delete();
            });

        return $partyWasTold;
    }
}
