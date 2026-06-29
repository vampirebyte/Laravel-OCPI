<?php

namespace Ocpi\Modules\Emsp\Credentials\Server\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ocpi\Models\Party;
use Ocpi\Support\Enums\OcpiServerErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

class DeleteController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $party = Party::where('code', Context::get('party_code'))->first();
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
            DB::connection(config('ocpi.database.connection'))
                ->transaction(function () use ($party) {
                    $party->registered = false;
                    $party->save();
                    $party->delete();

                    $party->roles()->delete();
                });

            return $this->ocpiSuccessResponse();
        } catch (Exception $e) {
            Log::channel('ocpi')->error($e->getMessage());

            return $this->ocpiServerErrorResponse(
                statusCode: OcpiServerErrorCode::PartyApiUnusable
            );
        }
    }
}
