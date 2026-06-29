<?php

namespace Ocpi\Modules\Emsp\Sessions\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class SessionCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $party_role_id,
        public string $id,
        public mixed $payload,
    ) {}
}
