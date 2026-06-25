<?php

namespace Ocpi\Modules\Cpo\Credentials\Client;

use Ocpi\Support\Client\Resource as OcpiResource;

class Resource extends OcpiResource
{
    public function post(array $payload): ?array
    {
        return $this->requestPostSend($payload);
    }

    public function put(array $payload): ?array
    {
        return $this->requestPutSend($payload);
    }
}
