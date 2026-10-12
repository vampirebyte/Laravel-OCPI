<?php

namespace Ocpi\Models\Cpo\Dto;

use Ocpi\Support\Payload;

/**
 * A part of a session in which the same prices applied.
 */
class ChargingPeriod
{
    /**
     * @param  array<string, float>  $dimensions  CdrDimensionType => volume, e.g. ['ENERGY' => 12.5, 'TIME' => 0.75]
     * @param  string|null  $tariffId  the tariff that applied (2.2.1 only)
     */
    public function __construct(
        public readonly string $startDateTime,
        public readonly array $dimensions,
        public readonly ?string $tariffId = null,
    ) {}

    public function toArray(string $version): array
    {
        $dimensions = [];
        foreach ($this->dimensions as $type => $volume) {
            $dimensions[] = ['type' => $type, 'volume' => round($volume, 4)];
        }

        return Payload::withoutNulls([
            'start_date_time' => $this->startDateTime,
            'dimensions' => $dimensions,
            'tariff_id' => version_compare($version, '2.2', '>=') ? $this->tariffId : null,
        ]);
    }
}
