<?php

namespace Ocpi\Modules\Cpo\Credentials\Actions\Party;

use Exception;
use Illuminate\Support\Facades\DB;
use Ocpi\Models\Party;
use Ocpi\Modules\Shared\Versions\Actions\PartyInformationAndDetailsSynchronizeAction;
use Throwable;

class PartyEndpointsRefreshAction
{
    public function __construct(
        private readonly PartyInformationAndDetailsSynchronizeAction $versionsSynchronize,
    ) {}

    /**
     * Read the endpoints of a registered Party again, should it have changed
     * them without sending a credentials update. Its OCPI version is kept: one
     * it no longer offers is thrown - moving to another version is a new
     * registration. A failing step is thrown and nothing is changed.
     *
     * Our CPO config picks the URL of a module listed twice (2.2+); a 2.1.1
     * Party lists each module once, whichever side of ours it registered with.
     */
    public function handle(Party $party): Party
    {
        throw_if(
            ! $party->registered || $party->version === null,
            new Exception('Party '.$party->code.' - Not registered, no endpoints to refresh.')
        );

        $version = $party->version;
        $connection = DB::connection(config('ocpi.database.connection'));

        try {
            $connection->beginTransaction();

            $party = $this->versionsSynchronize->handle($party, 'ocpi-cpo', $version);

            throw_if(
                $party->version !== $version,
                new Exception('Party '.$party->code.' - OCPI '.$version.' is no longer offered, register the Party again.')
            );

            $connection->commit();
        } catch (Throwable $exception) {
            $connection->rollBack();
            $party->refresh();

            throw $exception;
        }

        return $party;
    }
}
