<?php

namespace Ocpi\Models\Cpo\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Ocpi\Models\Cdrs\CpoCdr;
use Ocpi\Models\Cpo\Contracts\CdrRepository;
use Ocpi\Models\Party;

/**
 * GET cdrs from the CDRs stored with CdrStoreAction - the default, a host
 * application needs no CdrRepository of its own.
 */
class StoredCdrRepository implements CdrRepository
{
    public function getCdrs(Party $party, array $filters): Collection
    {
        $cdrs = new Collection;

        foreach ($this->cdrsOf($party, $filters)->orderBy('id')->offset($filters['offset'])->limit($filters['limit'])->get() as $cdr) {
            $cdrs->push($cdr->toOcpiObject());
        }

        return $cdrs;
    }

    public function countCdrs(Party $party, array $filters): int
    {
        return $this->cdrsOf($party, $filters)->count();
    }

    /**
     * @param  array{date_from?: string, date_to?: string}  $filters
     * @return Builder<CpoCdr>
     */
    private function cdrsOf(Party $party, array $filters): Builder
    {
        $query = CpoCdr::query()->where('party_id', $party->id);

        if (isset($filters['date_from'])) {
            $query->where('updated_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->where('updated_at', '<=', $filters['date_to']);
        }

        return $query;
    }
}
