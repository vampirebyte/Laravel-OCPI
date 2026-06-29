<?php

namespace Ocpi\Modules\Emsp\Locations\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class LocationRestored implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $party_role_id,
        public string $id,
    ) {}
}
