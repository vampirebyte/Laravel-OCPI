<?php

namespace Ocpi\Models\Cpo\Dto;

use Ocpi\Models\Commands\Enums\CommandType;
use Ocpi\Models\Party;

class CommandRequest
{
    public function __construct(
        public readonly Party $party,
        public readonly CommandType $type,

        /**
         * URL provided by the EMS where the final command result
         * must be POSTed asynchronously.
         */
        public readonly string $responseUrl,

        public readonly mixed $token = null,
        public readonly ?string $locationId = null,
        public readonly ?string $evseUid = null,
        public readonly ?string $connectorId = null,
        public readonly ?string $sessionId = null,
        public readonly ?string $reservationId = null,
        public readonly ?string $expiryDate = null,

        /**
         * OCPI 2.2.1 START_SESSION / RESERVE_NOW: reference the eMSP gives the
         * authorization, to be sent back on the Session and CDR.
         */
        public readonly ?string $authorizationReference = null,

        /**
         * The OCPI version the command was received on.
         */
        public readonly string $version = '2.2.1',
    ) {}
}
