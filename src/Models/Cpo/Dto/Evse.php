<?php

namespace Ocpi\Models\Cpo\Dto;

class Evse
{
    public function __construct(
        public readonly string $uid,
        public readonly string $evseId,
        public readonly string $status,
        public readonly array $connectors,
        public readonly ?string $lastUpdated = null,
    ) {}
}