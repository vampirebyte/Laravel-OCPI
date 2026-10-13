<?php

namespace Ocpi\Modules\Cpo\Credentials\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Ocpi\Models\Party;
use Ocpi\Modules\Cpo\Credentials\Actions\Party\PartyRegisterAction;

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
    public function handle(PartyRegisterAction $partyRegisterAction): int
    {
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

        try {
            $this->info('  - Call EMSP OCPI - GET Versions Information and Details, POST Credentials with a new Client Token');
            $party = $partyRegisterAction->handle($party, $registrationToken);
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        $this->info('EMSP Party "'.$party->code.'" registered successfully.');

        return Command::SUCCESS;
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
