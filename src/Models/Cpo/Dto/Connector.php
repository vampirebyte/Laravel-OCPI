<?php

namespace Ocpi\Models\Cpo\Dto;

class Connector
{
    public function __construct(
        public readonly string $id,
        public readonly string $standard,
        public readonly string $format,
        public readonly string $powerType,
        public readonly ?string $lastUpdated = null,
        public readonly ?int $maxElectricPower = null,
        public readonly ?array $tariffIds = null,

        /**
         * Bilateral/partner-specific extension fields (raw key => value
         * pairs), merged verbatim into the payload. Only ever serialized for
         * OCPI 2.2+, since custom extensions aren't part of the 2.1.1 wire
         * format.
         */
        public readonly ?array $extensions = null,
    ) {}

    public function toArray(string $version): array
    {
        $data = [
            'id' => $this->id,
            'standard' => $this->standard,
            'format' => $this->format,
            'power_type' => $this->powerType,
            'last_updated' => $this->lastUpdated,
        ];

        if ($this->maxElectricPower !== null) {
            $data['max_electric_power'] = $this->maxElectricPower;
        }

        if ($this->tariffIds !== null) {
            $data['tariff_ids'] = $this->tariffIds;
        }

        if ($this->extensions !== null && version_compare($version, '2.2', '>=')) {
            $data = array_merge($data, $this->extensions);
        }

        return $data;
    }
}
