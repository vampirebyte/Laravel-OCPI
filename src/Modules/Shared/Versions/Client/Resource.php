<?php

namespace Ocpi\Modules\Shared\Versions\Client;

use Ocpi\Support\Client\Resource as OcpiResource;

class Resource extends OcpiResource
{
    public function information(): ?array
    {
        return $this->requestGetSend();
    }

    public function details(): ?array
    {
        return $this->requestGetSend();
    }
}
