<?php

namespace Ocpi\Modules\Cpo\Credentials\Actions\Party;

use InvalidArgumentException;
use Ocpi\Models\Party;

class PartyInitializeAction
{
    /**
     * The Party of a partner we exchange credentials with, before the
     * exchange. A code we know comes back as the same Party: a deleted one
     * keeps its sessions and CDRs, one still waiting for its handshake gets
     * the new details - both with nothing left from their old connection.
     * A registered Party is refused.
     *
     * @param  array{name: string, code: string, url: string, server_token?: string|null, client_token?: string|null}  $input
     */
    public function handle(array $input): Party
    {
        $party = Party::withTrashed()->where('code', $input['code'])->first();

        if ($party === null) {
            return Party::create($input);
        }

        if (! $party->trashed() && $party->registered) {
            throw new InvalidArgumentException('Party '.$input['code'].' is already registered.');
        }

        if ($party->trashed()) {
            $party->restore();
        }

        $party->fill([
            'server_token' => null,
            'client_token' => null,
            'version' => null,
            'version_url' => null,
            'endpoints' => null,
            'registered' => false,
            ...$input,
        ]);
        $party->save();

        return $party;
    }
}
