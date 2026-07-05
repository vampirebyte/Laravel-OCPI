<?php

namespace Ocpi\Modules\Cpo\Commands\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ocpi\Models\Commands\Enums\CommandType;
use Ocpi\Models\Cpo\Contracts\CommandsContract;
use Ocpi\Models\Cpo\Dto\CommandRequest as DtoCommandRequest;
use Ocpi\Support\Server\Controllers\Controller;
use Illuminate\Support\Facades\Context;
use Ocpi\Models\Party;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Traits\Server\Response as ServerResponse;

class CommandsController extends Controller
{
    use ServerResponse;

    public function __construct(
        private readonly CommandsContract $repository,
    ) {}

    /**
     * POST /commands/{commandType}
     */
    public function handle(string $commandType, Request $request): JsonResponse
    {
        /** @var Party $party */
        $partyCode = Context::get('cpo_party_code');
        $party = Party::where('code', $partyCode)->first();

        if ($party) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: 'Invalid Authorization Token or Party.',
            );
        }

        $data = $request->validate([
            'response_url' => ['required', 'url'],
            'location_id' => ['sometimes', 'string'],
            'evse_uid' => ['sometimes', 'string'],
            'connector_id' => ['sometimes', 'string'],
            'token' => ['sometimes'],
            'session_id' => ['sometimes', 'string'],
            'reservation_id' => ['sometimes', 'string'],
            'expiry_date' => ['sometimes', 'date'],
        ]);

        $command = new DtoCommandRequest(
            party: $party,
            type: CommandType::from($commandType),
            responseUrl: $data['response_url'],
            locationId: $data['location_id'] ?? null,
            evseUid: $data['evse_uid'] ?? null,
            connectorId: $data['connector_id'] ?? null,
            token: $data['token'] ?? null,
            sessionId: $data['session_id'] ?? null,
            reservationId: $data['reservation_id'] ?? null,
            expiryDate: $data['expiry_date'] ?? null,
        );

        return $this->ocpiSuccessResponse(
            $this->repository->handle($command)
        );
    }
}
