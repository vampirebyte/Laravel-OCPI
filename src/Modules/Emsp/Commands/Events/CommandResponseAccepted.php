<?php

namespace Ocpi\Modules\Emsp\Commands\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class CommandResponseAccepted implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $party_role_id,
        public string $id,
        public string $type,
    ) {}
}
