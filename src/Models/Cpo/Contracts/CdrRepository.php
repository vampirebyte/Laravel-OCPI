<?php

namespace Ocpi\Models\Cpo\Contracts;

use Illuminate\Support\Collection;
use Ocpi\Models\Party;

/**
 * The Cdrs sender interface (GET cdrs) - implemented by the host
 * application. The CDRs of sessions charged to the eMSP.
 */
interface CdrRepository
{
    /**
     * @param  array{date_from?: string, date_to?: string, offset: int, limit: int}  $filters
     * @return Collection<int, OcpiObject>
     */
    public function getCdrs(Party $party, array $filters): Collection;

    /**
     * @param  array{date_from?: string, date_to?: string}  $filters
     */
    public function countCdrs(Party $party, array $filters): int;
}
