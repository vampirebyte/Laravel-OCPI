<?php

namespace Ocpi\Modules\Cpo\Credentials\Actions\Party;

use Ocpi\Models\Party;
use Ocpi\Models\PartyRole;

class PartyRolesSynchronizeAction
{
    /**
     * Replace the Party roles with the ones received in a Credentials object.
     *
     * @param  array<int, array{role: string, party_id: string, country_code: string, business_details: array<string, mixed>}>  $roles
     */
    public function handle(Party $party, array $roles): void
    {
        $party->loadMissing('roles');
        $keptRoleIds = [];

        foreach ($roles as $role) {
            $partyRole = $party->roles
                ->where('code', $role['party_id'])
                ->where('country_code', $role['country_code'])
                ->where('role', $role['role'])
                ->first();

            if ($partyRole === null) {
                $partyRole = new PartyRole;
                $partyRole->code = $role['party_id'];
                $partyRole->country_code = $role['country_code'];
                $partyRole->role = $role['role'];
            }

            $partyRole->business_details = $role['business_details'];
            $party->roles()->save($partyRole);

            $keptRoleIds[] = $partyRole->id;
        }

        $party->roles()->whereKeyNot($keptRoleIds)->delete();
        $party->unsetRelation('roles');
        $party->touch();
    }
}
