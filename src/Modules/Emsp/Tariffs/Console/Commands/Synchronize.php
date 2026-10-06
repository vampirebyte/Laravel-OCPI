<?php

namespace Ocpi\Modules\Emsp\Tariffs\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Ocpi\Models\Party;
use Ocpi\Modules\Emsp\Tariffs\Traits\HandlesTariff;
use Ocpi\Support\Client\Client;

class Synchronize extends Command
{
    use HandlesTariff;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocpi:tariffs:synchronize {--P|party=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize tariffs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting OCPI Tariffs synchronization');

        $optionParty = $this->option('party');

        $partyList = Party::with(['roles'])
            ->registered()
            ->when($optionParty, function (Builder $query) use ($optionParty) {
                $query->whereIn('code', explode(',', $optionParty));
            })
            ->get();

        if ($optionParty !== null && $partyList->count() !== count(explode(',', $optionParty))) {
            $this->error('Requested Party list could not be found.');

            return Command::FAILURE;
        }

        if ($partyList->pluck('roles')->flatten()->count() === 0) {
            $this->error('No Party to process.');

            return Command::FAILURE;
        }

        $hasError = false;

        foreach ($partyList as $party) {
            $this->info('  - Processing Party '.$party->code);

            $ocpiClient = new Client($party, 'tariffs');

            if (empty($ocpiClient->resolveBaseUrl())) {
                $this->warn('Party '.$party->code.' is not configured to use the Tariffs module.');

                continue;
            }

            foreach ($party->roles as $partyRole) {
                $this->info('    - Call '.$partyRole->code.' / '.$partyRole->country_code.' - OCPI - Tariffs GET');
                $ocpiTariffList = $ocpiClient->tariffs()->all() ?? [];

                $tariffProcessedList = [];

                $this->info('    - '.count($ocpiTariffList).' Tariff(s) retrieved');

                foreach ($ocpiTariffList as $ocpiTariff) {
                    $ocpiTariffId = $ocpiTariff['id'] ?? null;

                    if ($ocpiTariffId === null) {
                        $hasError = true;
                        $this->error('Tariff without ID skipped.');

                        continue;
                    }

                    DB::connection(config('ocpi.database.connection'))->beginTransaction();

                    $tariff = $this->tariffSearch(
                        party_role_id: $partyRole->id,
                        tariff_id: $ocpiTariffId,
                        withTrashed: true,
                    );

                    $this->info('      > Processing '.($tariff === null ? 'new' : 'existing').' Tariff '.$ocpiTariffId);

                    $isProcessed = $tariff === null
                        ? $this->tariffCreate(
                            payload: $ocpiTariff,
                            party_role_id: $partyRole->id,
                            tariff_id: $ocpiTariffId,
                        )
                        : $this->tariffReplace(
                            payload: $ocpiTariff,
                            tariff: $tariff,
                        );

                    if (! $isProcessed) {
                        $hasError = true;
                        $this->error('Error processing Tariff '.$ocpiTariffId.'.');

                        DB::connection(config('ocpi.database.connection'))->rollback();

                        continue;
                    }

                    $tariffProcessedList[] = $ocpiTariffId;

                    DB::connection(config('ocpi.database.connection'))->commit();
                }

                $this->info('    - '.count($tariffProcessedList).' Tariff(s) synchronized');
            }
        }

        return $hasError
            ? Command::FAILURE
            : Command::SUCCESS;
    }
}
