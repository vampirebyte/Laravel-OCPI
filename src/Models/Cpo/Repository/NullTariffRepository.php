<?php

namespace Ocpi\Models\Cpo\Repository;

use Illuminate\Support\Collection;
use Ocpi\Models\Cpo\Contracts\TariffRepository;
use Ocpi\Models\Party;

/**
 * Used while the host application binds no TariffRepository: nothing to list.
 */
class NullTariffRepository implements TariffRepository
{
    public function getTariffs(Party $party, array $filters): Collection
    {
        return new Collection;
    }

    public function countTariffs(Party $party, array $filters): int
    {
        return 0;
    }
}
