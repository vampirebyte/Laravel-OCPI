<?php

namespace Ocpi\Modules\Emsp\Credentials\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\DB;
use Ocpi\Models\Party;
use Ocpi\Modules\Emsp\Credentials\Actions\Party\SelfCredentialsGetAction;
use Ocpi\Modules\Emsp\Credentials\Validators\V2_1_1\CredentialsValidator;
use Ocpi\Modules\Shared\Versions\Actions\PartyInformationAndDetailsSynchronizeAction as VersionsPartyInformationAndDetailsSynchronizeAction;
use Ocpi\Support\Client\Client;

class Register extends Command implements PromptsForMissingInput
{
    protected $signature = 'ocpi:credentials:register {party_code}';

    protected $description = 'Credentials exchange with a new "Receiver" Party';

    public function handle(
        VersionsPartyInformationAndDetailsSynchronizeAction $versionsPartyInformationAndDetailsSynchronizeAction,
        SelfCredentialsGetAction $selfCredentialsGetAction,
    ) {
        $partyCode = $this->argument('party_code');
        $this->info('Starting credentials exchange with '.$partyCode);

        $party = Party::where('code', $partyCode)->first();
        if ($party === null) {
            $this->error('Party not found.');

            return Command::FAILURE;
        }

        if ($party->registered === true) {
            $this->error('Party already registered.');

            return Command::FAILURE;
        }

        try {
            DB::connection(config('ocpi.database.connection'))->beginTransaction();

            $this->info('  - Call Party OCPI - GET - Versions Information and Details, store OCPI endpoints');
            $party = $versionsPartyInformationAndDetailsSynchronizeAction->handle($party);

            $party->client_token = $party->generateToken();
            $this->info('  - Generate, store new OCPI Client Token: '.$party->client_token);
            $party->save();

            DB::connection(config('ocpi.database.connection'))->commit();

            DB::connection(config('ocpi.database.connection'))->beginTransaction();

            $this->info('  - Call Party OCPI - POST - Credentials endpoint with new Client Token');
            $ocpiClient = new Client($party, 'credentials');
            $credentialsPostData = $ocpiClient->credentials()->post($selfCredentialsGetAction->handle($party));
            $credentialsInput = CredentialsValidator::validate($credentialsPostData);

            $this->info('  - Store received OCPI Server Token: '.$credentialsInput['token'].', mark the Party as registered');
            $party->server_token = Party::decodeToken($credentialsInput['token'], $party);
            $party->registered = true;
            $party->save();

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
            'party_code' => 'Which Party should be registered?',
        ];
    }
}
