<?php

namespace Ocpi\Modules\Emsp\Locations\Server\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ocpi\Modules\Emsp\Locations\Traits\HandlesLocation;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

class PatchController extends Controller
{
    use HandlesLocation;

    public function __invoke(
        Request $request,
        string $country_code,
        string $party_id,
        string $location_id,
        ?string $evse_uid = null,
        ?string $connector_id = null,
    ): JsonResponse {
        try {
            $payload = $request->json()->all();

            // EVSE or Connector.
            if ($evse_uid !== null) {
                $locationEvse = $this->evseSearch(
                    party_role_id: Context::get('party_role_id'),
                    location_id: $location_id,
                    evse_uid: $evse_uid,
                    withTrashed: true,
                );

                if ($locationEvse === null || $locationEvse->locationWithTrashed?->id !== $location_id) {
                    return $this->ocpiClientErrorResponse(
                        statusCode: OcpiClientErrorCode::UnknownLocation,
                        statusMessage: 'Unknown Location or EVSE.',
                    );
                }

                // Updated EVSE.
                if ($connector_id === null) {
                    if (
                        ! DB::connection(config('ocpi.database.connection'))
                            ->transaction(function () use ($payload, $locationEvse) {
                                return $this->evseObjectUpdate(
                                    payload: $payload,
                                    locationEvse: $locationEvse,
                                );
                            })
                    ) {
                        return $this->ocpiClientErrorResponse(
                            statusCode: OcpiClientErrorCode::NotEnoughInformation,
                        );
                    }
                } // Updated Connector.
                else {
                    $locationConnector = $locationEvse
                        ->connectorsWithTrashed
                        ->where('id', $connector_id)
                        ->first();

                    if ($locationConnector === null) {
                        return $this->ocpiClientErrorResponse(
                            statusCode: OcpiClientErrorCode::UnknownLocation,
                            statusMessage: 'Unknown Connector.',
                        );
                    }

                    if (
                        ! DB::connection(config('ocpi.database.connection'))
                            ->transaction(function () use ($payload, $locationConnector, $locationEvse) {
                                return $this->connectorObjectUpdate(
                                    payload: $payload,
                                    locationConnector: $locationConnector,
                                    locationEvse: $locationEvse,
                                );
                            })
                    ) {
                        return $this->ocpiClientErrorResponse(
                            statusCode: OcpiClientErrorCode::NotEnoughInformation,
                        );
                    }
                }

            } // Location.
            else {
                $location = $this->locationSearch(
                    party_role_id: Context::get('party_role_id'),
                    location_id: $location_id,
                    withTrashed: true,
                );

                if ($location === null) {
                    return $this->ocpiClientErrorResponse(
                        statusCode: OcpiClientErrorCode::UnknownLocation,
                        statusMessage: 'Unknown Location.',
                    );
                }

                // Updated Location.
                if (
                    ! DB::connection(config('ocpi.database.connection'))
                        ->transaction(function () use ($payload, $location) {
                            return $this->locationObjectUpdate(
                                payload: $payload,
                                location: $location,
                            );
                        })
                ) {
                    return $this->ocpiClientErrorResponse(
                        statusCode: OcpiClientErrorCode::NotEnoughInformation,
                    );
                }
            }

            return $this->ocpiSuccessResponse();
        } catch (Exception $e) {
            Log::channel('ocpi')->error($e->getMessage());

            return $this->ocpiServerErrorResponse();
        }
    }
}
