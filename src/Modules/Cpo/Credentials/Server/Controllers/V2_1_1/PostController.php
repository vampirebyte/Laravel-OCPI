<?php

namespace Ocpi\Modules\Cpo\Credentials\Server\Controllers\V2_1_1;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Ocpi\Models\Party;
use Ocpi\Models\PartyRole;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Enums\OcpiServerErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

use Ocpi\Modules\Cpo\Credentials\Actions\Party\SelfCredentialsGetAction;
use Ocpi\Modules\Cpo\Credentials\Events;
use Ocpi\Modules\Cpo\Credentials\Validators\V2_1_1\CredentialsValidator;
use Ocpi\Modules\Shared\Versions\Actions\PartyInformationAndDetailsSynchronizeAction as VersionsPartyInformationAndDetailsSynchronizeAction;

class PostController extends Controller
{
    public function __invoke(
        Request $request,
        VersionsPartyInformationAndDetailsSynchronizeAction $versionsPartyInformationAndDetailsSynchronizeAction,
        SelfCredentialsGetAction $selfCredentialsGetAction,
    ): JsonResponse {
        try {
            $input = CredentialsValidator::validate($request->all());
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
                ->transaction(function () use ($party, $request, $input, $versionsPartyInformationAndDetailsSynchronizeAction) {
                    // Update Server Token, url for the Party and mark it as registered.
                    $party->server_token = Party::decodeToken($input['token'], $party);
                    $party->url = $request->input('url');
                    $party->registered = true;

                    // OCPI GET calls for Versions Information and Details of the Party, store OCPI endpoints.
                    $party = $versionsPartyInformationAndDetailsSynchronizeAction->handle($party, 'ocpi-cpo');

                    // Update PartyRole list.
                    $partyRole = $party->roles
                        ->where('code', $request->input('party_id'))
                        ->where('country_code', $request->input('country_code'))
                        ->first();

                    if ($partyRole === null) {
                        if ($party->roles->count() > 0) {
                            $party->roles()->delete();
                        }

                        $partyRole = new PartyRole;
                        $partyRole->fill([
                            'code' => $request->input('party_id'),
                            'role' => 'CPO',
                            'country_code' => $request->input('country_code'),
                            'business_details' => $request->input('business_details'),
                        ]);

                        $party->roles()->save($partyRole);
                    } else {
                        $partyRole->fill([
                            'role' => 'CPO',
                            'business_details' => $request->input('business_details'),
                        ]);

                        $partyRole->save();
                        $party->touch();
                    }

                    // Generate new Client Token for the Party.
                    $party->client_token = $party->generateToken();
                    $party->save();

                    return $party;
                });

            Events\CredentialsCreated::dispatch($party->id, $request->json()->all());

            return $this->ocpiCreatedResponse(
                $selfCredentialsGetAction->handle($party)
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
