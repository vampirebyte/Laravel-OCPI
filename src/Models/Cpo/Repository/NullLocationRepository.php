<?php

namespace Ocpi\Models\Cpo\Repositories;



use Illuminate\Support\Collection;
use Ocpi\Models\Cpo\Contracts\LocationRepository;
use Ocpi\Models\Cpo\Dto\Location;
use Ocpi\Models\Cpo\Dto\Evse;
use Ocpi\Models\Cpo\Dto\Connector;

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

    public function getLocations(array $filters = []): Collection
    {
        $this->fail();
    }
    public function countLocations(array $filters = []): int
    {
        $this->fail();
    }
    public function getLocation(string $locationId): ?Location
    {
        $this->fail();
    }
    public function getEvse(string $locationId, string $evseUid): ?Evse
    {
        $this->fail();
    }
    public function getConnector(string $locationId, string $evseUid, string $connectorId): ?Connector
    {
        $this->fail();
    }
}
