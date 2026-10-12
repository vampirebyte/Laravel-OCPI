<?php

namespace Ocpi\Models\Cpo\Repository;

use Illuminate\Support\Collection;
use Ocpi\Models\Cpo\Contracts\SessionRepository;
use Ocpi\Models\Party;

/**
 * Used while the host application binds no SessionRepository: nothing to list.
 */
class NullSessionRepository implements SessionRepository
{
    public function getSessions(Party $party, array $filters): Collection
    {
        return new Collection;
    }

    public function countSessions(Party $party, array $filters): int
    {
        return 0;
    }
}
