<?php

namespace Ocpi\Modules\Cpo\Tokens\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ocpi\Models\Party;
use Ocpi\Models\Tokens\CpoToken;
use Ocpi\Models\Tokens\Dto\Token;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

/**
 * The Tokens receiver interface: eMSPs push the tokens of their drivers
 * (PUT), update them (PATCH) and read back what we hold (GET), under
 * tokens/{country_code}/{party_id}/{token_uid} - in 2.2.1 with ?type=, RFID
 * by default. An eMSP can only manage tokens of its own country code / party id.
 */
class TokensController extends Controller
{
    public function show(Request $request, string $countryCode, string $partyId, string $tokenUid): JsonResponse
    {
        if (! $this->callerOwns($countryCode, $partyId)) {
            return $this->notOwned();
        }

        $token = $this->find($countryCode, $partyId, $tokenUid, $this->type($request));

        return $token
            ? $this->ocpiSuccessResponse($token->toDto()->toArray($this->version()))
            : $this->unknownToken();
    }

    public function put(Request $request, string $countryCode, string $partyId, string $tokenUid): JsonResponse
    {
        if (! $this->callerOwns($countryCode, $partyId)) {
            return $this->notOwned();
        }

        $validator = Validator::make($request->all(), [
            'uid' => ['required', 'string', 'max:36', Rule::in([$tokenUid])],
            'type' => ['required', Rule::in(['AD_HOC_USER', 'APP_USER', 'OTHER', 'RFID'])],
            'auth_id' => [Rule::requiredIf(version_compare($this->version(), '2.2', '<')), 'string', 'max:36'],
            'contract_id' => [Rule::requiredIf(version_compare($this->version(), '2.2', '>=')), 'string', 'max:36'],
            'issuer' => ['required', 'string', 'max:64'],
            'valid' => ['required', 'boolean'],
            'whitelist' => ['required', Rule::in(['ALWAYS', 'ALLOWED', 'ALLOWED_OFFLINE', 'NEVER'])],
            'last_updated' => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: $validator->errors()->first(),
            );
        }

        $this->store(Token::fromArray($request->all(), $countryCode, $partyId), $request->all());

        return $this->ocpiSuccessResponse();
    }

    /**
     * Only the fields sent change; last_updated is required.
     */
    public function patch(Request $request, string $countryCode, string $partyId, string $tokenUid): JsonResponse
    {
        if (! $this->callerOwns($countryCode, $partyId)) {
            return $this->notOwned();
        }

        $token = $this->find($countryCode, $partyId, $tokenUid, $this->type($request));
        if (! $token) {
            return $this->unknownToken();
        }

        if (! $request->filled('last_updated')) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: 'last_updated is required.',
            );
        }

        $merged = array_merge($token->object, $request->except(['uid', 'type', 'country_code', 'party_id']));
        $this->store(Token::fromArray($merged, $countryCode, $partyId), $merged);

        return $this->ocpiSuccessResponse();
    }

    /**
     * @param  array<string, mixed>  $object  the token as received
     */
    private function store(Token $token, array $object): void
    {
        CpoToken::updateOrCreate(
            [
                'country_code' => $token->countryCode,
                'party_code' => $token->partyId,
                'uid' => $token->uid,
                'type' => $token->type,
            ],
            [
                'party_id' => $this->caller()->id,
                'contract_id' => $token->contractId,
                'visual_number' => $token->visualNumber,
                'issuer' => $token->issuer,
                'group_id' => $token->groupId,
                'valid' => $token->valid,
                'whitelist' => $token->whitelist,
                'language' => $token->language,
                'last_updated' => $token->lastUpdated,
                'object' => $object,
            ],
        );
    }

    private function find(string $countryCode, string $partyId, string $tokenUid, string $type): ?CpoToken
    {
        return CpoToken::query()
            ->where('country_code', $countryCode)
            ->where('party_code', $partyId)
            ->where('uid', $tokenUid)
            ->where('type', $type)
            ->first();
    }

    /**
     * The calling eMSP has a role with this country code and party id.
     */
    private function callerOwns(string $countryCode, string $partyId): bool
    {
        return $this->caller()->roles()
            ->where('country_code', $countryCode)
            ->where('code', $partyId)
            ->exists();
    }

    private function caller(): Party
    {
        return Context::getHidden('cpo_party');
    }

    /**
     * 2.2.1 ?type=, RFID when not given (and always in 2.1.1).
     */
    private function type(Request $request): string
    {
        return (string) $request->query('type', 'RFID');
    }

    private function version(): string
    {
        return (string) Context::get('ocpi_version', '2.1.1');
    }

    private function unknownToken(): JsonResponse
    {
        return $this->ocpiClientErrorResponse(
            statusCode: OcpiClientErrorCode::UnknownToken,
            statusMessage: 'Unknown Token.',
            httpCode: 404,
        );
    }

    private function notOwned(): JsonResponse
    {
        return $this->ocpiClientErrorResponse(
            statusCode: OcpiClientErrorCode::InvalidParameters,
            statusMessage: 'The country code and party id are not yours.',
            httpCode: 403,
        );
    }
}
