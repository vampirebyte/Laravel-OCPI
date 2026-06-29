<?php

namespace Ocpi\Modules\Emsp\Versions\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\DB;
use Ocpi\Models\Party;
use Ocpi\Modules\Shared\Versions\Actions\PartyInformationAndDetailsSynchronizeAction as VersionsPartyInformationAndDetailsSynchronizeAction;

class Update extends Command implements PromptsForMissingInput
{
    protected $signature = 'ocpi:versions:update {party_code}';

    protected $description = 'Update Party OCPI version with the latest mutual version';

    public function handle(
        VersionsPartyInformationAndDetailsSynchronizeAction $versionsPartyInformationAndDetailsSynchronizeAction,
    ) {
        $partyCode = $this->argument('party_code');
        $this->info('Starting versions update with '.$partyCode);

        $party = Party::where('code', $partyCode)->first();
        if ($party === null) {
            $this->error('Party not found.');

            return Command::FAILURE;
        }

        try {
            DB::connection(config('ocpi.database.connection'))->beginTransaction();

            $this->info('  - Call Party OCPI - GET - Versions Information and Details, store OCPI endpoints');
            $party = $versionsPartyInformationAndDetailsSynchronizeAction->handle($party);
            $this->info('Latest mutual version configured: '.$party->version);

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
