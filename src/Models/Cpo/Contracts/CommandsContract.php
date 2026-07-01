<?php

namespace Ocpi\Models\Cpo\Contracts;

use Ocpi\Models\Cpo\Dto\CommandRequest as DtoCommandRequest;
use Ocpi\Models\Cpo\Dto\CommandResponse as DtoCommandResponse;

interface CommandsContract
{
    /**
     * Handle an OCPI command.
     *
     * The implementation is responsible for:
     * - validating business rules
     * - forwarding to OCPP/backend
     * - calling the response_url asynchronously
     */
    public function handle(DtoCommandRequest $command): DtoCommandResponse;
}