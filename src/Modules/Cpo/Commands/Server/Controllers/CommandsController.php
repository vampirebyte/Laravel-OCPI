<?php

namespace Ocpi\Modules\Cpo\Commands\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ocpi\Models\Commands\Enums\CommandType;
use Ocpi\Models\Cpo\Contracts\CommandsContract;
use Ocpi\Models\Cpo\Dto\CommandRequest as DtoCommandRequest;
use Ocpi\Models\Cpo\Dto\CommandResponse;
use Ocpi\Models\Party;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Server\Controllers\Controller;
use Ocpi\Support\Traits\Server\Response as ServerResponse;

class CommandsController extends Controller
{
    use ServerResponse;

    public function __construct(
        private readonly CommandsContract $repository,
    ) {}

    /**
     * POST /commands/{commandType}
     *
     * The answer is a CommandResponse in the shape of the version the command
     * arrived on; the final result follows asynchronously on response_url.
     */
    public function handle(string $commandType, Request $request): JsonResponse
    {
        $version = (string) Context::get('ocpi_version', '2.1.1');
        $party = Party::where('code', Context::get('cpo_party_code'))->first();

        if (! $party) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: 'Invalid Authorization Token or Party.',
                httpCode: 401,
            );
        }

        $type = CommandType::tryFrom($commandType);
        if ($type === null) {
            return $this->ocpiSuccessResponse((new CommandResponse('NOT_SUPPORTED'))->toArray($version));
        }

        $validator = Validator::make($request->all(), $this->rules($type));
        if ($validator->fails()) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: $validator->errors()->first(),
            );
        }
        $data = $validator->validated();

        $command = new DtoCommandRequest(
            party: $party,
            type: $type,
            responseUrl: $data['response_url'],
            // the whole token: validated() would keep only the validated token.uid
            token: $request->input('token'),
            locationId: $data['location_id'] ?? null,
            evseUid: $data['evse_uid'] ?? null,
            connectorId: $data['connector_id'] ?? null,
            sessionId: isset($data['session_id']) ? (string) $data['session_id'] : null,
            reservationId: isset($data['reservation_id']) ? (string) $data['reservation_id'] : null,
            expiryDate: $data['expiry_date'] ?? null,
            authorizationReference: $data['authorization_reference'] ?? null,
            version: $version,
        );

        return $this->ocpiSuccessResponse(
            $this->repository->handle($command)->toArray($version)
        );
    }

    /**
     * The command bodies of OCPI 2.1.1 and 2.2.1 together: 2.1.1 sends no
     * connector_id with START_SESSION, 2.2.1 may add authorization_reference.
     * Reservation and session ids are strings in OCPI, numbers are accepted.
     *
     * @return array<string, array<int, mixed>>
     */
    private function rules(CommandType $type): array
    {
        return [
            'response_url' => ['required', 'url'],
            'location_id' => [
                'nullable', Rule::requiredIf(in_array($type, [CommandType::START_SESSION, CommandType::RESERVE_NOW, CommandType::UNLOCK_CONNECTOR], true)),
                'string',
            ],
            'evse_uid' => ['nullable', Rule::requiredIf($type === CommandType::UNLOCK_CONNECTOR), 'string'],
            'connector_id' => ['nullable', Rule::requiredIf($type === CommandType::UNLOCK_CONNECTOR), 'string'],
            'token' => ['nullable', Rule::requiredIf(in_array($type, [CommandType::START_SESSION, CommandType::RESERVE_NOW], true)), 'array'],
            'token.uid' => [Rule::requiredIf(in_array($type, [CommandType::START_SESSION, CommandType::RESERVE_NOW], true)), 'string'],
            'session_id' => ['nullable', Rule::requiredIf($type === CommandType::STOP_SESSION)],
            'reservation_id' => ['nullable', Rule::requiredIf(in_array($type, [CommandType::RESERVE_NOW, CommandType::CANCEL_RESERVATION], true))],
            'expiry_date' => ['nullable', Rule::requiredIf($type === CommandType::RESERVE_NOW), 'date', 'after:now'],
            'authorization_reference' => ['nullable', 'string', 'max:36'],
        ];
    }
}
