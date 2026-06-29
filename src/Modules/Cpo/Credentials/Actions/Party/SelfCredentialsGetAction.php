<?php

namespace Ocpi\Modules\Cpo\Credentials\Actions\Party;

use Ocpi\Models\Party;

class SelfCredentialsGetAction
{
    public function handle(Party $party): ?array
    {
        $versionsUrl = rtrim(config('app.url'), '/').'/ocpi/cpo/versions';

        return [
            'url' => $versionsUrl,
            'token' => $party->encoded_client_token,
            'party_id' => config('ocpi-cpo.party.party_id'),
            'country_code' => config('ocpi-cpo.party.country_code'),
            'business_details' => [
                'name' => config('ocpi-cpo.party.business_details.name'),
                'website' => config('ocpi-cpo.party.business_details.website'),
            ],
        ];
    }
}
