<?php

namespace Ocpi\Models\Cpo\Dto;

use Ocpi\Support\Payload;

/**
 * The CPO's immediate answer to a command, serialized for the version the
 * command was received on:
 * - 2.1.1 CommandResponse: {result}, result one of NOT_SUPPORTED, REJECTED,
 *   ACCEPTED, TIMEOUT, UNKNOWN_SESSION;
 * - 2.2.1 CommandResponse: {result, timeout, message?} - no TIMEOUT result,
 *   `timeout` the seconds the eMSP waits for the asynchronous result.
 */
class CommandResponse
{
    public function __construct(
        public string $result,
        public ?int $timeout = null,
        public ?string $message = null,
    ) {}

    public function toArray(string $version): array
    {
        if (version_compare($version, '2.2', '<')) {
            return ['result' => $this->result];
        }

        return Payload::withoutNulls([
            'result' => $this->result === 'TIMEOUT' ? 'REJECTED' : $this->result,
            'timeout' => $this->timeout ?? (int) config('ocpi-cpo.commands.timeout', 30),
            // 2.2.1 DisplayText
            'message' => $this->message !== null ? [['language' => 'en', 'text' => $this->message]] : null,
        ]);
    }
}
