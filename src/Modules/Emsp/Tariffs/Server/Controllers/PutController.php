<?php

namespace Ocpi\Modules\Emsp\Tariffs\Server\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ocpi\Modules\Emsp\Tariffs\Traits\HandlesTariff;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

class PutController extends Controller
{
    use HandlesTariff;

    public function __invoke(
        Request $request,
        string $country_code,
        string $party_id,
        string $tariff_id,
    ): JsonResponse {
        try {
            $payload = $request->json()->all();

            $tariff = $this->tariffSearch(
                party_role_id: Context::get('party_role_id'),
                tariff_id: $tariff_id,
                withTrashed: true,
            );

            if (
                ! DB::connection(config('ocpi.database.connection'))
                    ->transaction(function () use ($payload, $tariff, $tariff_id) {
                        // New Tariff.
                        if ($tariff === null) {
                            return $this->tariffCreate(
                                payload: $payload,
                                party_role_id: Context::get('party_role_id'),
                                tariff_id: $tariff_id,
                            );
                        }

                        // Replaced Tariff.
                        return $this->tariffReplace(
                            payload: $payload,
                            tariff: $tariff,
                        );
                    })
            ) {
                return $this->ocpiClientErrorResponse(
                    statusCode: OcpiClientErrorCode::NotEnoughInformation,
                );
            }

            return $this->ocpiSuccessResponse();
        } catch (Exception $e) {
            Log::channel('ocpi')->error($e->getMessage());

            return $this->ocpiServerErrorResponse();
        }
    }
}
