<?php

namespace Ocpi\Modules\Emsp\Locations\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Ocpi\Models\Party;
use Ocpi\Modules\Emsp\Locations\Traits\HandlesLocation;
use Ocpi\Support\Client\Client;

class Synchronize extends Command
{
    use HandlesLocation;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocpi:locations:synchronize {--P|party=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize locations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting OCPI Locations synchronization');

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

            $ocpiClient = new Client($party, 'locations');

            if (empty($ocpiClient->resolveBaseUrl())) {
                $this->warn('Party '.$party->code.' is not configured to use the Locations module.');

                continue;
            }

            foreach ($party->roles as $partyRole) {
                $this->info('    - Call '.$partyRole->code.' / '.$partyRole->country_code.' - OCPI - Locations GET');
                $ocpiLocationList = $ocpiClient->locations()->all();

                $locationProcessedList = [];

                $this->info('    - '.count($ocpiLocationList).' Location(s) retrieved');

                foreach ($ocpiLocationList as $ocpiLocation) {
                    $ocpiLocationId = $ocpiLocation['id'] ?? null;

                    DB::connection(config('ocpi.database.connection'))->beginTransaction();

                    $location = $this->locationSearch(
                        party_role_id: $partyRole->id,
                        location_id: $ocpiLocationId,
                        withTrashed: true,
                    );

                    $this->info('      > Processing '.($location === null ? 'new' : 'existing').' Location '.$ocpiLocationId);

                    // New Location.
                    if ($location === null) {
                        if (! $this->locationCreate(
                            payload: $ocpiLocation,
                            party_role_id: $partyRole->id,
                            location_id: $ocpiLocationId,
                        )) {
                            $hasError = true;
                            $this->error('Error creating Location '.$ocpiLocationId.'.');

                            DB::connection(config('ocpi.database.connection'))->rollback();

                            continue;
                        }
                    } else {
                        // Replaced Location.
                        if (! $this->locationReplace(
                            payload: $ocpiLocation,
                            location: $location,
                        )) {
                            $hasError = true;
                            $this->error('Error replacing Location '.$ocpiLocationId.'.');

                            DB::connection(config('ocpi.database.connection'))->rollback();

                            continue;
                        }
                    }

                    $locationProcessedList[] = $ocpiLocationId;

                    DB::connection(config('ocpi.database.connection'))->commit();

                }

                $this->info('    - '.count($locationProcessedList).' Location(s) synchronized');
            }

            return $hasError
                ? Command::FAILURE
                : Command::SUCCESS;
        }
    }
}
