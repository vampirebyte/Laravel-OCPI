<?php

namespace Ocpi\Modules\Emsp\Locations\Client;

use Ocpi\Support\Client\Resource as OcpiResource;

class Resource extends OcpiResource
{
    public function all(): ?array
    {
        return $this->requestGetSend();
    }

    public function location(string $locationId): ?array
    {
        return $this->requestGetSend($locationId);
    }

    public function locationEvse(string $locationId, string $evseUid): ?array
    {
        return $this->requestGetSend(implode('/', func_get_args()));
    }

    public function locationEvseConnector(string $locationId, string $evseUid, string $connectorId): ?array
    {
        return $this->requestGetSend(implode('/', func_get_args()));
    }
}
