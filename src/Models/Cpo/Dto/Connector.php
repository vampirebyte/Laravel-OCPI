<?php

namespace Ocpi\Models\Cpo\Dto;

class Connector
{
    public function __construct(
        public readonly string $id,
        public readonly string $standard,
        public readonly string $format,
        public readonly string $powerType,
        public readonly int $voltage,
        public readonly int $amperage,
        public readonly ?string $lastUpdated = null,
    ) {}
}