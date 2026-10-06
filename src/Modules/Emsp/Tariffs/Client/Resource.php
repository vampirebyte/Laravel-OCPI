<?php

namespace Ocpi\Modules\Emsp\Tariffs\Client;

use Ocpi\Support\Client\Resource as OcpiResource;

class Resource extends OcpiResource
{
    public function all(): ?array
    {
        return $this->requestGetSend();
    }
}
