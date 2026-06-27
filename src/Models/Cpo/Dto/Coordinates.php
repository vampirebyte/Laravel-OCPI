<?php

namespace Ocpi\Models\Cpo\Dto;

class Coordinates
{
    public function __construct(
        public readonly string $latitude,
        public readonly string $longitude,
    ) {}
}