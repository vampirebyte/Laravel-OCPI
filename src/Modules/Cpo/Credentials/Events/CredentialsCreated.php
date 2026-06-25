<?php

namespace Ocpi\Modules\Cpo\Credentials\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class CredentialsCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $party_id,
        public mixed $payload,
    ) {}
}
