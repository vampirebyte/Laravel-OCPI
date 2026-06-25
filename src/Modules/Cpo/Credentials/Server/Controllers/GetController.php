<?php

namespace Ocpi\Modules\Cpo\Credentials\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Ocpi\Models\Party;
use Ocpi\Modules\Credentials\Actions\Party\SelfCredentialsGetAction;
use Ocpi\Support\Enums\OcpiServerErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

class GetController extends Controller
{
    public function __invoke(Request $request, SelfCredentialsGetAction $selfCredentialsGetAction): JsonResponse
    {
        $party = Party::where('code', Context::get('party_code'))->first();
        if ($party === null) {
            return $this->ocpiServerErrorResponse(
                statusCode: OcpiServerErrorCode::PartyApiUnusable
            );
        }

        $data = $selfCredentialsGetAction->handle($party);

        return $data
            ? $this->ocpiSuccessResponse($data)
            : $this->ocpiServerErrorResponse(
                statusCode: OcpiServerErrorCode::PartyApiUnusable
            );
    }
}
