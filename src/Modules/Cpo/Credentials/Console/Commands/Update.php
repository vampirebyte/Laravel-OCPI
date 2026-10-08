<?php

namespace Ocpi\Modules\Cpo\Credentials\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\DB;
use Ocpi\Models\Party;
use Ocpi\Modules\Cpo\Credentials\Actions\Party\PartyRolesSynchronizeAction;
use Ocpi\Modules\Cpo\Credentials\Actions\Party\SelfCredentialsGetAction;
use Ocpi\Modules\Cpo\Credentials\Validators\CredentialsValidator;
use Ocpi\Modules\Shared\Versions\Actions\PartyInformationAndDetailsSynchronizeAction as VersionsPartyInformationAndDetailsSynchronizeAction;
use Ocpi\Support\Client\Client;

class Update extends Command implements PromptsForMissingInput
{
    protected $signature = 'ocpi:credentials:cpo_update {party_code} {--without_new_client_token}';

    protected $description = 'Credentials update with an "EMSP" Party';

    public function handle(
        VersionsPartyInformationAndDetailsSynchronizeAction $versionsPartyInformationAndDetailsSynchronizeAction,
        PartyRolesSynchronizeAction $partyRolesSynchronizeAction,
        SelfCredentialsGetAction $selfCredentialsGetAction,
    ) {
        $partyCode = $this->argument('party_code');
        $generateNewClientToken = ! ($this->option('without_new_client_token') ?? false);
        $this->info('Starting credentials update with '.$partyCode.($generateNewClientToken ? ' with' : ' without').' new OCPI Client Token');

        $party = Party::where('code', $partyCode)->first();
        if ($party === null) {
            $this->error('EMSP Party not found.');

            return Command::FAILURE;
        }

        if ($party->registered === false) {
            $this->error('EMSP Party not registered.');

            return Command::FAILURE;
        }

        try {
            DB::connection(config('ocpi.database.connection'))->beginTransaction();

            $this->info('  - Call EMSP OCPI - GET - Versions Information and Details, store OCPI endpoints');
            $party = $versionsPartyInformationAndDetailsSynchronizeAction->handle($party, 'ocpi-cpo');

            if ($generateNewClientToken) {
                $party->client_token = $party->generateToken();
                $this->info('  - Generate, store new OCPI Client Token: '.$party->client_token);
                $party->save();
            }

            DB::connection(config('ocpi.database.connection'))->commit();

            DB::connection(config('ocpi.database.connection'))->beginTransaction();

            $this->info('  - Call EMSP OCPI - PUT - Credentials endpoint with new Client Token');
            $ocpiClient = new Client($party, 'credentials');
            $credentialsPutData = $ocpiClient->credentials()->put($selfCredentialsGetAction->handle($party));
            $credentialsInput = CredentialsValidator::validate($credentialsPutData ?? [], $party->version);

            $this->info('  - Store received OCPI Server Token: '.$credentialsInput['token']);
            $party->server_token = $credentialsInput['token'];
            $party->save();

            $partyRolesSynchronizeAction->handle($party, $credentialsInput['roles']);

            DB::connection(config('ocpi.database.connection'))->commit();

            return Command::SUCCESS;
        } catch (Exception $e) {
            DB::connection(config('ocpi.database.connection'))->rollback();

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'party_code' => 'Which EMSP Party should be updated?',
        ];
    }
}
