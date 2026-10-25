<?php

namespace Ocpi\Models\Cpo\Contracts;

use Illuminate\Support\Collection;
use Ocpi\Models\Cpo\Dto\Connector;
use Ocpi\Models\Cpo\Dto\Evse;
use Ocpi\Models\Cpo\Dto\Location;
use Ocpi\Models\Party;

/**
 * The Locations sender interface (GET locations) - implemented by the host
 * application. Every method gets the calling party, so the host returns only
 * the locations that party may see, like TariffRepository.
 */
interface LocationRepository
{
    /**
     * The locations the party may see, for filtering and pagination.
     *
     * @param  array{
     *     date_from?: string,
     *     date_to?: string,
     *     offset?: int,
     *     limit?: int
     * } $filters
     * @return Collection<Location>
     */
    public function getLocations(Party $party, array $filters = []): Collection;

    public function countLocations(Party $party, array $filters = []): int;

    public function getLocation(Party $party, string $locationId): ?Location;

    public function getEvse(Party $party, string $locationId, string $evseUid): ?Evse;

    public function getConnector(Party $party, string $locationId, string $evseUid, string $connectorId): ?Connector;
}
