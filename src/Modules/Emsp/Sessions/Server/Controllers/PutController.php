<?php

namespace Ocpi\Modules\Emsp\Sessions\Server\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ocpi\Modules\Emsp\Locations\Traits\HandlesLocation;
use Ocpi\Modules\Emsp\Sessions\Traits\HandlesSession;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

class PutController extends Controller
{
    use HandlesLocation,
        HandlesSession;

    public function __invoke(
        Request $request,
        string $country_code,
        string $party_id,
        string $session_id,
    ): JsonResponse {
        try {
            $payload = $request->json()->all();

            $session = $this->sessionSearch(
                session_id: $session_id,
                party_role_id: Context::get('party_role_id'),
            );

            // New Session.
            if ($session === null) {
                // Find LocationEvse.
                $locationEvse = null;
                $location_id = data_get($payload, 'location.id');
                $location_evse_uid = data_get($payload, 'location.evses.0.uid');
                if ($location_id && $location_evse_uid) {
                    $locationEvse = $this->evseSearch(
                        party_role_id: Context::get('party_role_id'),
                        location_id: $location_id,
                        evse_uid: $location_evse_uid,
                    );
                }

                // New Session.
                if (
                    ! DB::connection(config('ocpi.database.connection'))
                        ->transaction(function () use ($payload, $session_id, $locationEvse) {
                            return $this->sessionCreate(
                                payload: $payload,
                                party_role_id: Context::get('party_role_id'),
                                session_id: $session_id,
                                location_evse_emsp_id: $locationEvse?->emsp_id,
                            );
                        })
                ) {
                    return $this->ocpiClientErrorResponse(
                        statusCode: OcpiClientErrorCode::NotEnoughInformation,
                    );
                }
            } else {
                // Replaced Session.
                if (
                    ! DB::connection(config('ocpi.database.connection'))
                        ->transaction(function () use ($payload, $session) {
                            return $this->sessionReplace(
                                payload: $payload,
                                session: $session,
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
