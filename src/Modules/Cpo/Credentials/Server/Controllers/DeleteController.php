<?php

namespace Ocpi\Modules\Cpo\Credentials\Server\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Ocpi\Models\Party;
use Ocpi\Modules\Shared\Credentials\Actions\PartyDeleteAction;
use Ocpi\Support\Enums\OcpiServerErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

class DeleteController extends Controller
{
    public function __invoke(Request $request, PartyDeleteAction $partyDeleteAction): JsonResponse
    {
        $party = Party::where('code', Context::get('cpo_party_code'))->first();
        if ($party === null) {
            return $this->ocpiServerErrorResponse(
                statusCode: OcpiServerErrorCode::PartyApiUnusable,
                statusMessage: 'Client not found.',
                httpCode: 405,
            );
        }

        if ($party->registered === false) {
            return $this->ocpiServerErrorResponse(
                statusCode: OcpiServerErrorCode::PartyApiUnusable,
                statusMessage: 'Client not registered.',
                httpCode: 405,
            );
        }

        try {
            // The EMSP itself ends the connection - nobody to tell.
            $partyDeleteAction->handle($party, tellParty: false);

            return $this->ocpiSuccessResponse();
        } catch (Exception $e) {
            Log::channel('ocpi')->error($e->getMessage());

            return $this->ocpiServerErrorResponse(
                statusCode: OcpiServerErrorCode::PartyApiUnusable
            );
        }
    }
}
