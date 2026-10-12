<?php

namespace Ocpi\Models\Cpo\Contracts;

use Illuminate\Support\Collection;
use Ocpi\Models\Party;

/**
 * The Sessions sender interface (GET sessions) - implemented by the host
 * application. The sessions charged to the eMSP.
 */
interface SessionRepository
{
    /**
     * @param  array{date_from?: string, date_to?: string, offset: int, limit: int}  $filters
     * @return Collection<int, OcpiObject>
     */
    public function getSessions(Party $party, array $filters): Collection;

    /**
     * @param  array{date_from?: string, date_to?: string}  $filters
     */
    public function countSessions(Party $party, array $filters): int;
}
