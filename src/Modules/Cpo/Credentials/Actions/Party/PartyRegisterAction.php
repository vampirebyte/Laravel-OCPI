<?php

namespace Ocpi\Modules\Cpo\Credentials\Actions\Party;

use Illuminate\Support\Facades\DB;
use Ocpi\Models\Party;
use Ocpi\Modules\Cpo\Credentials\Validators\CredentialsValidator;
use Ocpi\Modules\Shared\Versions\Actions\PartyInformationAndDetailsSynchronizeAction;
use Ocpi\Support\Client\Client;
use Throwable;

class PartyRegisterAction
{
    public function __construct(
        private readonly PartyInformationAndDetailsSynchronizeAction $versionsSynchronize,
        private readonly PartyRolesSynchronizeAction $rolesSynchronize,
        private readonly SelfCredentialsGetAction $selfCredentialsGet,
    ) {}

    /**
     * The credentials exchange started by us, the CPO: with the partner's
     * registration token (Token A), read its versions and endpoints, send it
     * our credentials with a new token of ours (Token B), and keep the token
     * it answers with (Token C) and its roles. A failing step is thrown - the
     * Party then stays unregistered.
     */
    public function handle(Party $party, string $registrationToken): Party
    {
        $connection = DB::connection(config('ocpi.database.connection'));

        try {
            $connection->beginTransaction();

            $party->server_token = $registrationToken;
            $party->save();

            $party = $this->versionsSynchronize->handle($party, 'ocpi-cpo');

            $party->client_token = $party->generateToken();
            $party->save();

            $connection->commit();

            $connection->beginTransaction();

            $credentials = (new Client($party, 'credentials'))->credentials()->post($this->selfCredentialsGet->handle($party));
            $credentialsInput = CredentialsValidator::validate($credentials ?? [], $party->version);

            $party->server_token = $credentialsInput['token'];
            $party->registered = true;
            $party->save();

            $this->rolesSynchronize->handle($party, $credentialsInput['roles']);

            $connection->commit();
        } catch (Throwable $exception) {
            $connection->rollBack();

            throw $exception;
        }

        return $party;
    }
}
