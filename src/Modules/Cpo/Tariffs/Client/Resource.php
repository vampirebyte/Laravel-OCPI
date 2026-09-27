<?php

namespace Ocpi\Modules\Cpo\Tariffs\Client;

use Ocpi\Support\Client\Resource as OcpiResource;

class Resource extends OcpiResource
{
    public function push(array $payload, string $countryCode, string $partyId, string $tariffId): array|string|null
    {
        return $this->requestPutSend(
            payload: $payload,
            endpoint: "{$countryCode}/{$partyId}/{$tariffId}",
        );
    }

    public function remove(string $countryCode, string $partyId, string $tariffId): array|string|null
    {
        return $this->requestDeleteSend(
            endpoint: "{$countryCode}/{$partyId}/{$tariffId}",
        );
    }
}
