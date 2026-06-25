<?php

namespace Ocpi\Modules\Cpo\Credentials\Actions\Party;

use Illuminate\Support\Facades\Route;
use Ocpi\Models\Party;

class SelfCredentialsGetAction
{
    public function handle(Party $party): ?array
    {
        $urlRoute = config('ocpi.server.routing.name_prefix').'versions.information';

        return Route::has($urlRoute)
            ? [
                'url' => route($urlRoute),
                'token' => $party->encoded_client_token,
                'party_id' => config('ocpi-cpo.party.party_id'),
                'country_code' => config('ocpi-cpo.party.country_code'),
                'business_details' => [
                    'name' => config('ocpi-cpo.party.business_details.name'),
                    'website' => config('ocpi-cpo.party.business_details.website'),
                ],
            ]
            : null;
    }
}
