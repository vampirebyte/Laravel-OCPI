<?php

namespace Ocpi\Models\Cpo\Dto;

use Ocpi\Models\Commands\Enums\CommandType;

/**
 * Immediate OCPI response returned to the EMS.
 */
class CommandRequest
{
    public function __construct(
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
    ) {}
}
