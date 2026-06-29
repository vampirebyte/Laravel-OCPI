<?php

namespace Ocpi\Modules\Emsp\Locations\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class LocationEvseUpdated implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $party_role_id,
        public string $location_id,
        public string $uid,
        public mixed $payload,
    ) {}
}
