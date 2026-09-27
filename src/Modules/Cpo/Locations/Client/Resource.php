<?php

namespace Ocpi\Modules\Cpo\Locations\Client;

use Ocpi\Support\Client\Resource as OcpiResource;

class Resource extends OcpiResource
{
    public function push(array $payload, string $countryCode, string $partyId, string $locationId): array|string|null
    {
        return $this->requestPutSend(
            payload: $payload,
            endpoint: "{$countryCode}/{$partyId}/{$locationId}",
        );
    }

    public function pushEvse(array $payload, string $countryCode, string $partyId, string $locationId, string $evseUid): array|string|null
    {
        return $this->requestPatchSend(
            payload: $payload,
            endpoint: "{$countryCode}/{$partyId}/{$locationId}/{$evseUid}",
        );
    }

    public function pushConnector(array $payload, string $countryCode, string $partyId, string $locationId, string $evseUid, string $connectorId): array|string|null
    {
        return $this->requestPatchSend(
            payload: $payload,
            endpoint: "{$countryCode}/{$partyId}/{$locationId}/{$evseUid}/{$connectorId}",
        );
    }
}
