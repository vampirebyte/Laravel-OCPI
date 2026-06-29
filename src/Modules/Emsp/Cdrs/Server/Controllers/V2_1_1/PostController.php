<?php

namespace Ocpi\Modules\Emsp\Cdrs\Server\Controllers\V2_1_1;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ocpi\Models\Party;
use Ocpi\Modules\Emsp\Cdrs\Traits\HandlesCdr;
use Ocpi\Modules\Emsp\Locations\Traits\HandlesLocation;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Enums\OcpiServerErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

class PostController extends Controller
{
    use HandlesCdr,
        HandlesLocation;

    public function __invoke(
        Request $request,
    ): JsonResponse {
        try {
            $partyCode = Context::get('party_code');

            $party = Party::with(['roles'])->where('code', $partyCode)->first();
            if ($party === null || $party->roles->count() === 0) {
                return $this->ocpiServerErrorResponse(
                    statusCode: OcpiServerErrorCode::PartyApiUnusable,
                    statusMessage: 'Client not found.',
                    httpCode: 405,
                );
            }

            $partyRoleId = $party->roles->first()->id;

            $payload = $request->json()->all();

            // Verify CDR not already exists.
            $cdr = $this->cdrSearch(
                cdr_id: $payload['id'] ?? null,
                party_role_id: $partyRoleId,
            );

            if ($cdr) {
                return $this->ocpiClientErrorResponse(
                    statusCode: OcpiClientErrorCode::InvalidParameters,
                    statusMessage: 'CDR already exists.',
                );
            }

            // Find LocationEvse.
            $locationEvse = null;
            $location_id = data_get($payload, 'location.id');
            $location_evse_uid = data_get($payload, 'location.evses.0.uid');
            if ($location_id && $location_evse_uid) {
                $locationEvse = $this->evseSearch(
                    party_role_id: $partyRoleId,
                    location_id: $location_id,
                    evse_uid: $location_evse_uid,
                );
            }

            // New CDR.
            $cdr = DB::connection(config('ocpi.database.connection'))
                ->transaction(function () use ($payload, $partyRoleId, $locationEvse) {
                    return $this->cdrCreate(
                        payload: $payload,
                        party_role_id: $partyRoleId,
                        location_evse_emsp_id: $locationEvse?->emsp_id,
                    );
                });

            return $cdr
                // Add Location header with CDR GET URL.
                ? $this->ocpiSuccessResponse()
                    ->header('Location', $this->cdrRoute($cdr))
                : $this->ocpiClientErrorResponse(
                    statusCode: OcpiClientErrorCode::NotEnoughInformation,
                );
        } catch (Exception $e) {
            Log::channel('ocpi')->error($e->getMessage());

            return $this->ocpiServerErrorResponse();
        }
    }
}
