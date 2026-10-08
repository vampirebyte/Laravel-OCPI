<?php

namespace Ocpi\Modules\Cpo\Credentials\Server\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Ocpi\Models\Party;
use Ocpi\Modules\Cpo\Credentials\Actions\Party\PartyRolesSynchronizeAction;
use Ocpi\Modules\Cpo\Credentials\Actions\Party\SelfCredentialsGetAction;
use Ocpi\Modules\Cpo\Credentials\Events;
use Ocpi\Modules\Cpo\Credentials\Validators\CredentialsValidator;
use Ocpi\Modules\Shared\Versions\Actions\PartyInformationAndDetailsSynchronizeAction as VersionsPartyInformationAndDetailsSynchronizeAction;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Enums\OcpiServerErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

class PostController extends Controller
{
    public function __invoke(
        Request $request,
        string $version,
        VersionsPartyInformationAndDetailsSynchronizeAction $versionsPartyInformationAndDetailsSynchronizeAction,
        PartyRolesSynchronizeAction $partyRolesSynchronizeAction,
        SelfCredentialsGetAction $selfCredentialsGetAction,
    ): JsonResponse {
        try {
            $input = CredentialsValidator::validate($request->all(), $version);
            $partyCode = Context::get('cpo_party_code');

            $party = Party::with(['roles'])->where('code', $partyCode)->first();
            if ($party === null) {
                return $this->ocpiServerErrorResponse(
                    statusCode: OcpiServerErrorCode::PartyApiUnusable,
                    statusMessage: 'EMSP Client not found.',
                    httpCode: 405,
                );
            }

            if ($party->registered === true) {
                return $this->ocpiServerErrorResponse(
                    statusCode: OcpiServerErrorCode::PartyApiUnusable,
                    statusMessage: 'EMSP Client already registered.',
                    httpCode: 405,
                );
            }

            $party = DB::connection(config('ocpi.database.connection'))
                ->transaction(function () use ($party, $input, $version, $versionsPartyInformationAndDetailsSynchronizeAction, $partyRolesSynchronizeAction) {
                    // Update Server Token, url for the Party and mark it as registered.
                    $party->server_token = $input['token'];
                    $party->url = $input['url'];
                    $party->registered = true;

                    // OCPI GET calls for Versions Information and Details of the Party, store OCPI endpoints.
                    $party = $versionsPartyInformationAndDetailsSynchronizeAction->handle($party, 'ocpi-cpo', $version);

                    $partyRolesSynchronizeAction->handle($party, $input['roles']);

                    // Generate new Client Token for the Party.
                    $party->client_token = $party->generateToken();
                    $party->save();

                    return $party;
                });

            Events\CredentialsCreated::dispatch($party->id, $request->json()->all());

            return $this->ocpiCreatedResponse(
                $selfCredentialsGetAction->handle($party, $version)
            );
        } catch (ValidationException $e) {
            Log::channel('ocpi')->error($e->getMessage());

            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: $e->getMessage(),
            );
        } catch (Exception $e) {
            Log::channel('ocpi')->error($e->getMessage());

            return $this->ocpiServerErrorResponse(
                statusCode: OcpiServerErrorCode::PartyApiUnusable,
            );
        }
    }
}
