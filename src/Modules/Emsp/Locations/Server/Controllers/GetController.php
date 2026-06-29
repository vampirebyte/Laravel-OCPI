<?php

namespace Ocpi\Modules\Emsp\Locations\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Ocpi\Modules\Emsp\Locations\Traits\HandlesLocation;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

class GetController extends Controller
{
    use HandlesLocation;

    public function __invoke(
        Request $request,
        ?string $country_code = null,
        ?string $party_id = null,
        ?string $location_id = null,
        ?string $evse_uid = null,
        ?string $connector_id = null,
    ): JsonResponse {
        if ($location_id === null) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::NotEnoughInformation,
                statusMessage: 'Location ID is missing.',
            );
        }

        $location = $this->locationSearch(
            party_role_id: Context::get('party_role_id'),
            location_id: $location_id,
        );

        if ($location === null) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::UnknownLocation,
                statusMessage: 'Unknown Location.',
            );
        }

        $data = null;

        if ($evse_uid === null && $connector_id === null) {
            $data = $location->object;
            $data['evses'] = $location
                ->evses
                ->map(function ($evse) {
                    $evse->object['connectors'] = $evse
                        ->connectors
                        ->map(function ($connector) {
                            return $connector->object;
                        });

                    return $evse->object;
                });
        } elseif ($evse_uid !== null) {
            $locationEvse = $location
                ->evses
                ->where('uid', $evse_uid)
                ->first();

            if ($locationEvse === null) {
                return $this->ocpiClientErrorResponse(
                    statusCode: OcpiClientErrorCode::UnknownLocation,
                    statusMessage: 'Unknown EVSE.',
                );
            }

            if ($connector_id === null) {
                $data = $locationEvse->object;
                $data['connectors'] = $locationEvse
                    ->connectors
                    ->map(function ($connector) {
                        return $connector->object;
                    });
            } else {
                $locationConnector = $locationEvse
                    ->connectors
                    ->where('id', $connector_id)
                    ->first();

                if ($locationConnector === null) {
                    return $this->ocpiClientErrorResponse(
                        statusCode: OcpiClientErrorCode::UnknownLocation,
                        statusMessage: 'Unknown Connector.',
                    );
                }

                $data = $locationConnector->object;
            }
        }

        return $data
            ? $this->ocpiSuccessResponse($data)
            : $this->ocpiServerErrorResponse();
    }
}
