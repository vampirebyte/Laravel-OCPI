<?php

namespace Ocpi\Models\Cpo\Dto;

class Location
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $name,
        public readonly string $address,
        public readonly string $city,
        public readonly string $postalCode,
        public readonly string $country,
        public readonly Coordinates $coordinates,
        public readonly array $evses,
        public readonly string $lastUpdated,
    ) {}
}