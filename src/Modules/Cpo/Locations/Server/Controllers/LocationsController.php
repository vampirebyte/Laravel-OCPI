<?php

namespace Ocpi\Modules\Cpo\Locations\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ocpi\Models\Cpo\Contracts\LocationRepository;
use Ocpi\Support\Server\Controllers\Controller;
use Ocpi\Support\Enums\OcpiClientErrorCode;

class LocationsController extends Controller
{
    public function __construct(
        private readonly LocationRepository $repository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to']);
        $offset  = (int) $request->query('offset', 0);
        $limit   = (int) $request->query('limit', 100);

        $filters['offset'] = $offset;
        $filters['limit']  = $limit;

        $locations = $this->repository->getLocations($filters);
        $total     = $this->repository->countLocations($filters);

        return $this->ocpiSuccessResponse($locations)
            ->header('X-Total-Count', $total)
            ->header('X-Limit', $limit)
            ->header('X-Offset', $offset);
    }

    public function show(string $locationId): JsonResponse
    {
        $location = $this->repository->getLocation($locationId);

        if (!$location) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::NotEnoughInformation,
                statusMessage: 'Location not found',
            );
        }

        return $this->ocpiSuccessResponse($location);
    }

    public function showEvse(string $locationId, string $evseUid): JsonResponse
    {
        $evse = $this->repository->getEvse($locationId, $evseUid);

        if (!$evse) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::NotEnoughInformation,
                statusMessage: 'EVSE not found',
            );
        }

        return $this->ocpiSuccessResponse($evse);
    }

    public function showConnector(string $locationId, string $evseUid, string $connectorId): JsonResponse
    {
        $connector = $this->repository->getConnector($locationId, $evseUid, $connectorId);

        if (!$connector) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::NotEnoughInformation,
                statusMessage: 'Connector',
            );
        }

        return $this->ocpiSuccessResponse($connector);
    }
}
