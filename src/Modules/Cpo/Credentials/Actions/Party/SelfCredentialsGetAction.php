<?php

namespace Ocpi\Modules\Cpo\Credentials\Actions\Party;

use Ocpi\Models\Party;
use Ocpi\Modules\Cpo\Credentials\Validators\CredentialsValidator;

class SelfCredentialsGetAction
{
    /**
     * Build our Credentials object in the shape of the given OCPI version (the Party version by default).
     * The token is sent as-is: only the Authorization header is base64-encoded in OCPI 2.2+.
     */
    public function handle(Party $party, ?string $version = null): ?array
    {
        $version ??= $party->version;
        $versionsUrl = rtrim(config('app.url'), '/').'/ocpi/cpo/versions';

        $businessDetails = [
            'name' => config('ocpi-cpo.party.business_details.name'),
            'website' => config('ocpi-cpo.party.business_details.website'),
        ];

        if (CredentialsValidator::hasRoles($version)) {
            return [
                'url' => $versionsUrl,
                'token' => $party->client_token,
                'roles' => [[
                    'role' => 'CPO',
                    'party_id' => config('ocpi-cpo.party.party_id'),
                    'country_code' => config('ocpi-cpo.party.country_code'),
                    'business_details' => $businessDetails,
                ]],
            ];
        }

        return [
            'url' => $versionsUrl,
            'token' => $party->client_token,
            'party_id' => config('ocpi-cpo.party.party_id'),
            'country_code' => config('ocpi-cpo.party.country_code'),
            'business_details' => $businessDetails,
        ];
    }
}
