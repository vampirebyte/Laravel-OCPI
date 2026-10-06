<?php

namespace Ocpi\Modules\Emsp\Tariffs\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Ocpi\Modules\Emsp\Tariffs\Traits\HandlesTariff;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

class GetController extends Controller
{
    use HandlesTariff;

    public function __invoke(
        Request $request,
        ?string $country_code = null,
        ?string $party_id = null,
        ?string $tariff_id = null,
    ): JsonResponse {
        if ($tariff_id === null) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::NotEnoughInformation,
                statusMessage: 'Tariff ID is missing.',
            );
        }

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

        return $this->ocpiSuccessResponse($tariff->object);
    }
}
