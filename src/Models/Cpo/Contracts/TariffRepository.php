<?php

namespace Ocpi\Models\Cpo\Contracts;

use Illuminate\Support\Collection;
use Ocpi\Models\Party;

/**
 * The Tariffs sender interface (GET tariffs) - implemented by the host
 * application. The tariffs the eMSP may see.
 */
interface TariffRepository
{
    /**
     * @param  array{date_from?: string, date_to?: string, offset: int, limit: int}  $filters
     * @return Collection<int, OcpiObject>
     */
    public function getTariffs(Party $party, array $filters): Collection;

    /**
     * @param  array{date_from?: string, date_to?: string}  $filters
     */
    public function countTariffs(Party $party, array $filters): int;
}
