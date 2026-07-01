<?php

namespace Ocpi\Models\Cpo\Dto;

/**
 * Represents OCPI Commands object.
 *
 * This is the standard response returned by CPO after a command request.
 */
class CommandResponse
{
    public function __construct(
        public string $result,        // ACCEPTED | REJECTED | UNKNOWN_SESSION | NOT_SUPPORTED
        public ?string $timeout = null
    ) {}
}