<?php

namespace Ocpi\Modules\Cpo\Commands\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ocpi\Models\Commands\Enums\CommandType;
use Ocpi\Models\Cpo\Contracts\CommandsContract;
use Ocpi\Models\Cpo\Dto\CommandRequest as DtoCommandRequest;
use Ocpi\Support\Server\Controllers\Controller;
use Illuminate\Support\Facades\Context;
use Illuminate\Validation\Rule;
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

        if (!$party) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: 'Invalid Authorization Token or Party.',
            );
        }
        $type = CommandType::from($commandType);
        $data = $request->validate([
            'response_url' => ['required', 'url'],
            'location_id' => [
                Rule::requiredIf(in_array($type, [
                    CommandType::START_SESSION,
                    CommandType::RESERVE_NOW,
                    CommandType::UNLOCK_CONNECTOR,
                ])),
                'string',
            ],
            'evse_uid' => [
                Rule::requiredIf($type === CommandType::UNLOCK_CONNECTOR),
                'string',
            ],
            'connector_id' => [
                Rule::requiredIf($type === CommandType::UNLOCK_CONNECTOR),
                'string',
            ],
            'token' => [
                Rule::requiredIf(in_array($type, [
                    CommandType::START_SESSION,
                    CommandType::RESERVE_NOW,
                ])),
                'array',
            ],
            'session_id' => [
                Rule::requiredIf($type === CommandType::STOP_SESSION),
                'string',
            ],
            'reservation_id' => [
                Rule::requiredIf(in_array($type, [
                    CommandType::RESERVE_NOW,
                    CommandType::CANCEL_RESERVATION,
                ])),
                'integer',
            ],
            'expiry_date' => [
                Rule::requiredIf($type === CommandType::RESERVE_NOW),
                'date',
                'after:now',
            ],
        ]);

        $command = new DtoCommandRequest(
            party: $party,
            type: $type,
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
