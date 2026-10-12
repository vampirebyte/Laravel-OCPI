<?php

namespace Ocpi\Modules\Cpo\Tokens\Events;

use Illuminate\Support\Facades\Log;
use Ocpi\Models\Party;
use Ocpi\Models\Tokens\Dto\AuthorizationInfo;
use Ocpi\Support\Client\Client;
use Throwable;

/**
 * Asks the eMSP owning a token whether it may charge here now (real-time
 * authorization). The location reference is sent in the eMSP's version:
 * 2.1.1 LocationReferences has connector_ids, 2.2.1 has not. Any failure -
 * unknown token, eMSP unreachable - counts as NOT_ALLOWED.
 */
class AuthorizeToken
{
    /**
     * @param  array<int, string>  $evseUids
     * @param  array<int, string>  $connectorIds
     */
    public function authorize(Party $party, string $tokenUid, string $type, ?string $locationId = null, array $evseUids = [], array $connectorIds = []): AuthorizationInfo
    {
        $locationReferences = null;
        if ($locationId !== null) {
            $locationReferences = ['location_id' => $locationId, 'evse_uids' => $evseUids];
            if (version_compare((string) $party->version, '2.2', '<')) {
                $locationReferences['connector_ids'] = $connectorIds;
            }
        }

        try {
            $info = (new Client($party, 'tokens'))
                ->cpoTokens()
                ->authorize($tokenUid, $type, $locationReferences);
        } catch (Throwable $exception) {
            Log::channel('ocpi')->warning('Party '.$party->code.' - token authorization failed', [
                'token_uid' => $tokenUid,
                'error' => $exception->getMessage(),
            ]);

            return new AuthorizationInfo('NOT_ALLOWED');
        }

        return new AuthorizationInfo(
            allowed: (string) ($info['allowed'] ?? 'NOT_ALLOWED'),
            authorizationReference: isset($info['authorization_reference']) ? (string) $info['authorization_reference'] : null,
        );
    }
}
