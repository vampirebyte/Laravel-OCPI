<?php

namespace Ocpi\Models\Cpo\Dto;

use Ocpi\Support\Payload;

/**
 * An OCPI Connector, serialized in the shape of the version asked for:
 * - 2.1.1: voltage, amperage and a single tariff_id;
 * - 2.2.1: max_voltage, max_amperage, optional max_electric_power and tariff_ids.
 * Voltage is line to neutral for AC (per phase), the DC voltage otherwise.
 */
class Connector
{
    public function __construct(
        public readonly string $id,
        public readonly string $standard,
        public readonly string $format,
        public readonly string $powerType,
        public readonly int $maxVoltage,
        public readonly int $maxAmperage,
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
        // required - a connector that never changed is as recent as now
        $lastUpdated = $this->lastUpdated ?? now()->toIso8601ZuluString();

        if (version_compare($version, '2.2', '<')) {
            return Payload::withoutNulls([
                'id' => $this->id,
                'standard' => $this->standard,
                'format' => $this->format,
                'power_type' => $this->powerType,
                'voltage' => $this->maxVoltage,
                'amperage' => $this->maxAmperage,
                'tariff_id' => $this->tariffIds[0] ?? null,
                'last_updated' => $lastUpdated,
            ]);
        }

        $data = Payload::withoutNulls([
            'id' => $this->id,
            'standard' => $this->standard,
            'format' => $this->format,
            'power_type' => $this->powerType,
            'max_voltage' => $this->maxVoltage,
            'max_amperage' => $this->maxAmperage,
            'max_electric_power' => $this->maxElectricPower,
            'tariff_ids' => $this->tariffIds ?: null,
            'last_updated' => $lastUpdated,
        ]);

        if ($this->extensions !== null) {
            $data = array_merge($data, $this->extensions);
        }

        return $data;
    }
}
