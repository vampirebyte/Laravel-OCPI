<?php

namespace Ocpi\Models\Cpo\Contracts;

use Illuminate\Support\Collection;
use Ocpi\Models\Cpo\Dto\Location;
use Ocpi\Models\Cpo\Dto\Evse;
use Ocpi\Models\Cpo\Dto\Connector;

interface LocationRepository
{
    /**
     * Returns all location with ocpi support for filtering and pagination.
     *
     * @param  array{
     *     date_from?: string,
     *     date_to?: string,
     *     offset?: int,
     *     limit?: int
     * } $filters
     * @return Collection<Location>
     */
    public function getLocations(array $filters = []): Collection;

    public function countLocations(array $filters = []): int;

    public function getLocation(string $locationId): ?Location;

    public function getEvse(string $locationId, string $evseUid): ?Evse;

    public function getConnector(string $locationId, string $evseUid, string $connectorId): ?Connector;
}