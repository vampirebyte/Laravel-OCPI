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

class DeleteController extends Controller
{
    use HandlesTariff;

    public function __invoke(
        Request $request,
        string $country_code,
        string $party_id,
        string $tariff_id,
    ): JsonResponse {
        try {
            $tariff = $this->tariffSearch(
                party_role_id: Context::get('party_role_id'),
                tariff_id: $tariff_id,
            );

            if ($tariff === null) {
                return $this->ocpiClientErrorResponse(
                    statusCode: OcpiClientErrorCode::InvalidParameters,
                    statusMessage: 'Unknown Tariff.',
                );
            }

            DB::connection(config('ocpi.database.connection'))
                ->transaction(function () use ($tariff) {
                    return $this->tariffRemove($tariff);
                });

            return $this->ocpiSuccessResponse();
        } catch (Exception $e) {
            Log::channel('ocpi')->error($e->getMessage());

            return $this->ocpiServerErrorResponse();
        }
    }
}
