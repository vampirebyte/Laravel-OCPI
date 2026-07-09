<?php

namespace Ocpi\Modules\Cpo\Sessions\Client;

use Ocpi\Support\Client\Resource as OcpiResource;

class Resource extends OcpiResource
{
    public function push(array $payload, string $countryCode, string $partyId, string $sessionId): array|string|null
    {
        return $this->requestPutSend(
            payload: $payload,
            endpoint: "{$countryCode}/{$partyId}/{$sessionId}",
        );
    }
}
