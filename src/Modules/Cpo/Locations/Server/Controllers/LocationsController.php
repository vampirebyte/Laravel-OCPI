<?php

namespace Ocpi\Modules\Cpo\Locations\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Ocpi\Models\Cpo\Contracts\LocationRepository;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Server\Controllers\Controller;
use Ocpi\Support\Traits\Server\Paginates;

/**
 * The Locations sender interface, the same routes in 2.1.1 and 2.2.1 - the
 * objects are serialized for the version the request came in on.
 */
class LocationsController extends Controller
{
    use Paginates;

    public function __construct(
        private readonly LocationRepository $repository
    ) {}

    public function index(Request $request): JsonResponse
    {
        [$offset, $limit] = $this->paginationWindow($request);
        $filters = [...$request->only(['date_from', 'date_to']), 'offset' => $offset, 'limit' => $limit];

        $locations = [];
        foreach ($this->repository->getLocations($filters) as $location) {
            $locations[] = $location->toArray($this->version());
        }

        return $this->paginatedResponse($request, $locations, $this->repository->countLocations($filters), $offset, $limit);
    }

    public function show(string $locationId): JsonResponse
    {
        $location = $this->repository->getLocation($locationId);

        return $location
            ? $this->ocpiSuccessResponse($location->toArray($this->version()))
            : $this->unknownLocation('Location not found.');
    }

    public function showEvse(string $locationId, string $evseUid): JsonResponse
    {
        $evse = $this->repository->getEvse($locationId, $evseUid);

        return $evse
            ? $this->ocpiSuccessResponse($evse->toArray($this->version()))
            : $this->unknownLocation('EVSE not found.');
    }

    public function showConnector(string $locationId, string $evseUid, string $connectorId): JsonResponse
    {
        $connector = $this->repository->getConnector($locationId, $evseUid, $connectorId);

        return $connector
            ? $this->ocpiSuccessResponse($connector->toArray($this->version()))
            : $this->unknownLocation('Connector not found.');
    }

    /**
     * OCPI 2003: unknown Location (EVSE, Connector).
     */
    private function unknownLocation(string $message): JsonResponse
    {
        return $this->ocpiClientErrorResponse(
            statusCode: OcpiClientErrorCode::UnknownLocation,
            statusMessage: $message,
            httpCode: 404,
        );
    }

    /**
     * The OCPI version of the request, set by the version route group.
     */
    private function version(): string
    {
        return (string) Context::get('ocpi_version', '2.1.1');
    }
}
