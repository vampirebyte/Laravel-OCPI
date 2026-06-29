<?php

namespace Ocpi\Modules\Emsp\Cdrs\Server\Controllers\V2_1_1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Ocpi\Models\Party;
use Ocpi\Modules\Emsp\Cdrs\Traits\HandlesCdr;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Enums\OcpiServerErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

class GetController extends Controller
{
    use HandlesCdr;

    public function __invoke(
        Request $request,
        ?string $cdr_emsp_id = null,
    ): JsonResponse {
        if ($cdr_emsp_id === null) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::NotEnoughInformation,
                statusMessage: 'eMSP CDR ID is missing.',
            );
        }

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

        $cdr = $this->cdrSearch(
            cdr_emsp_id: $cdr_emsp_id,
            party_role_id: $partyRoleId,
        );

        if ($cdr === null) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: 'Unknown CDR.',
            );
        }

        $data = $cdr->object;

        return $data
            ? $this->ocpiSuccessResponse($data)
            : $this->ocpiServerErrorResponse();
    }
}
