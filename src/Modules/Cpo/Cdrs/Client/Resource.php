<?php

namespace Ocpi\Modules\Cpo\Cdrs\Client;

use Ocpi\Support\Client\Resource as OcpiResource;

class Resource extends OcpiResource
{
    public function push(array $payload): array|string|null
    {
        return $this->requestPostSend(
            payload: $payload,
        );
    }
}
