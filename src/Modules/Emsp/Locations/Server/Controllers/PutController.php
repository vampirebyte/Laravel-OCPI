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

class PutController extends Controller
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

            $location = $this->locationSearch(
                party_role_id: Context::get('party_role_id'),
                location_id: $location_id,
                withTrashed: true,
            );

            // EVSE or Connector.
            if ($evse_uid !== null) {
                $locationEvse = $this->evseSearch(
                    party_role_id: Context::get('party_role_id'),
                    location_id: $location_id,
                    evse_uid: $evse_uid,
                    withTrashed: true,
                );

                if (
                    ($locationEvse !== null && $location?->id !== $location_id)
                    || ($locationEvse === null && $connector_id !== null)
                ) {
                    return $this->ocpiClientErrorResponse(
                        statusCode: OcpiClientErrorCode::UnknownLocation,
                        statusMessage: 'Unknown Location or EVSE.',
                    );
                }

                // New EVSE.
                if ($locationEvse === null) {
                    if (
                        ! DB::connection(config('ocpi.database.connection'))
                            ->transaction(function () use ($payload, $location, $evse_uid) {
                                return $this->evseCreate(
                                    payload: $payload,
                                    location: $location,
                                    evse_uid: $evse_uid,
                                );
                            })
                    ) {
                        return $this->ocpiClientErrorResponse(
                            statusCode: OcpiClientErrorCode::NotEnoughInformation,
                        );
                    }
                } else {
                    // Replaced EVSE.
                    if ($connector_id === null) {
                        if (
                            ! DB::connection(config('ocpi.database.connection'))
                                ->transaction(function () use ($payload, $locationEvse) {
                                    return $this->evseReplace(
                                        payload: $payload,
                                        locationEvse: $locationEvse,
                                    );
                                })
                        ) {
                            return $this->ocpiClientErrorResponse(
                                statusCode: OcpiClientErrorCode::NotEnoughInformation,
                            );
                        }
                    } // New or replaced Connector.
                    else {
                        if (
                            ! DB::connection(config('ocpi.database.connection'))
                                ->transaction(function () use ($payload, $connector_id, $locationEvse) {
                                    return $this->connectorCreateOrReplace(
                                        payload: $payload,
                                        connector_id: $connector_id,
                                        locationEvse: $locationEvse,
                                    );
                                })
                        ) {
                            return $this->ocpiClientErrorResponse(
                                statusCode: OcpiClientErrorCode::NotEnoughInformation,
                            );
                        }
                    }
                }

            } // Location.
            else {
                // New Location.
                if ($location === null) {
                    if (
                        ! DB::connection(config('ocpi.database.connection'))
                            ->transaction(function () use ($payload, $location_id) {
                                return $this->locationCreate(
                                    payload: $payload,
                                    party_role_id: Context::get('party_role_id'),
                                    location_id: $location_id,
                                );
                            })
                    ) {
                        return $this->ocpiClientErrorResponse(
                            statusCode: OcpiClientErrorCode::NotEnoughInformation,
                        );
                    }
                } else {
                    // Replaced Location.
                    if (
                        ! DB::connection(config('ocpi.database.connection'))
                            ->transaction(function () use ($payload, $location) {
                                return $this->locationReplace(
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
            }

            return $this->ocpiSuccessResponse();
        } catch (Exception $e) {
            Log::channel('ocpi')->error($e->getMessage());

            return $this->ocpiServerErrorResponse();
        }
    }
}
