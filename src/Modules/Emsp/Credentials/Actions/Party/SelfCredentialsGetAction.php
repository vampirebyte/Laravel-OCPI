<?php

namespace Ocpi\Modules\Emsp\Credentials\Actions\Party;

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
                'party_id' => config('ocpi-emsp.party.party_id'),
                'country_code' => config('ocpi-emsp.party.country_code'),
                'business_details' => [
                    'name' => config('ocpi-emsp.party.business_details.name'),
                    'website' => config('ocpi-emsp.party.business_details.website'),
                ],
            ]
            : null;
    }
}
