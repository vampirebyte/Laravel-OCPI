<?php

namespace Ocpi\Modules\Cpo\Credentials\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\DB;
use Ocpi\Models\Party;
use Ocpi\Models\PartyRole;
use Ocpi\Modules\Cpo\Credentials\Actions\Party\SelfCredentialsGetAction;
use Ocpi\Modules\Cpo\Credentials\Validators\V2_1_1\CredentialsValidator;
use Ocpi\Modules\Shared\Versions\Actions\PartyInformationAndDetailsSynchronizeAction as VersionsPartyInformationAndDetailsSynchronizeAction;
use Ocpi\Support\Client\Client;

class Register extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocpi:credentials:cpo_register
                            {party_code : Code of the EMSP Party created with ocpi:credentials:cpo_initialize}
                            {--token= : Registration token (Token A) provided by the EMSP}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CPO-initiated credentials exchange with an EMSP Party';

    /**
     * Execute the console command.
     */
    public function handle(
        VersionsPartyInformationAndDetailsSynchronizeAction $versionsPartyInformationAndDetailsSynchronizeAction,
        SelfCredentialsGetAction $selfCredentialsGetAction,
    ): int {
        $partyCode = $this->argument('party_code');
        $this->info('Starting credentials exchange with EMSP '.$partyCode);

        $party = Party::with(['roles'])->where('code', $partyCode)->first();
        if ($party === null) {
            $this->error('EMSP Party not found.');

            return Command::FAILURE;
        }

        if ($party->registered === true) {
            $this->error('EMSP Party already registered.');

            return Command::FAILURE;
        }

        $registrationToken = $this->option('token') ?? $party->server_token;
        if (blank($registrationToken)) {
            $registrationToken = $this->ask('Registration token (Token A) provided by the EMSP');
        }

        if (blank($registrationToken)) {
            $this->error('A registration token is required to call the EMSP.');

            return Command::FAILURE;
        }

        $connection = DB::connection(config('ocpi.database.connection'));

        try {
            $connection->beginTransaction();

            $party->server_token = $registrationToken;
            $party->save();

            $this->info('  - Call EMSP OCPI - GET - Versions Information and Details, store OCPI endpoints');
            $party = $versionsPartyInformationAndDetailsSynchronizeAction->handle($party, 'ocpi-cpo');

            $party->client_token = $party->generateToken();
            $this->info('  - Generate, store new OCPI Client Token: '.$party->client_token);
            $party->save();

            $connection->commit();

            $connection->beginTransaction();

            $this->info('  - Call EMSP OCPI - POST - Credentials endpoint with new Client Token');
            $ocpiClient = new Client($party, 'credentials');
            $credentialsPostData = $ocpiClient->credentials()->post($selfCredentialsGetAction->handle($party));
            $credentialsInput = CredentialsValidator::validate($credentialsPostData ?? []);

            $this->info('  - Store received OCPI Server Token: '.$credentialsInput['token'].', mark the EMSP Party as registered');
            $party->server_token = Party::decodeToken($credentialsInput['token'], $party);
            $party->registered = true;
            $party->save();

            $this->syncPartyRole($party, $credentialsInput);

            $connection->commit();

            $this->info('EMSP Party "'.$party->code.'" registered successfully.');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $connection->rollBack();

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Store the EMSP role returned in the credentials response.
     *
     * @param  array{party_id: string, country_code: string, business_details: array<string, mixed>}  $credentialsInput
     */
    protected function syncPartyRole(Party $party, array $credentialsInput): void
    {
        $partyRole = $party->roles
            ->where('code', $credentialsInput['party_id'])
            ->where('country_code', $credentialsInput['country_code'])
            ->first();

        if ($partyRole === null) {
            $party->roles()->delete();
            $partyRole = new PartyRole;
            $partyRole->code = $credentialsInput['party_id'];
            $partyRole->country_code = $credentialsInput['country_code'];
        }

        $partyRole->fill([
            'role' => 'EMSP',
            'business_details' => $credentialsInput['business_details'],
        ]);

        $party->roles()->save($partyRole);
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'party_code' => 'Which EMSP Party should be registered?',
        ];
    }
}
