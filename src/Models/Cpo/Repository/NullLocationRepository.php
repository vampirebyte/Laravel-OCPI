<?php

namespace Ocpi\Models\Cpo\Repository;



use Illuminate\Support\Collection;
use Ocpi\Models\Cpo\Contracts\LocationRepository;
use Ocpi\Models\Cpo\Dto\Location;
use Ocpi\Models\Cpo\Dto\Evse;
use Ocpi\Models\Cpo\Dto\Connector;
use Ocpi\Models\Party;

class NullLocationRepository implements LocationRepository
{
    private function fail(): never
    {
        throw new \RuntimeException(
            "OcpiCpo: LocationRepository is not bound.\n" .
                "Run: php artisan ocpi-cpo:install\n" .
                "Then bind the implementation in AppServiceProvider."
        );
    }

    public function getLocations(Party $party, array $filters = []): Collection
    {
        $this->fail();
    }
    public function countLocations(Party $party, array $filters = []): int
    {
        $this->fail();
    }
    public function getLocation(Party $party, string $locationId): ?Location
    {
        $this->fail();
    }
    public function getEvse(Party $party, string $locationId, string $evseUid): ?Evse
    {
        $this->fail();
    }
    public function getConnector(Party $party, string $locationId, string $evseUid, string $connectorId): ?Connector
    {
        $this->fail();
    }
}
