<?php

namespace Ocpi\Models\Tariffs\Dto;

class TariffRestrictions
{
    /**
     * Restriction fields defined by OCPI, mapped to their properties.
     */
    private const FIELDS = [
        'start_time' => 'startTime',
        'end_time' => 'endTime',
        'start_date' => 'startDate',
        'end_date' => 'endDate',
        'min_kwh' => 'minKwh',
        'max_kwh' => 'maxKwh',
        'min_power' => 'minPower',
        'max_power' => 'maxPower',
        'min_duration' => 'minDuration',
        'max_duration' => 'maxDuration',
        'day_of_week' => 'dayOfWeek',
    ];

    public function __construct(
        public readonly ?string $startTime = null,
        public readonly ?string $endTime = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?array $dayOfWeek = null,

        /**
         * Bilateral/partner-specific extension fields (raw key => value
         * pairs), merged verbatim into the payload, for restrictions outside
         * the OCPI 2.2.1 spec (e.g. a partner's own grace-period fields).
         */
        public readonly ?array $extensions = null,

        public readonly ?float $minKwh = null,
        public readonly ?float $maxKwh = null,
        public readonly ?float $minPower = null,
        public readonly ?float $maxPower = null,
        public readonly ?int $minDuration = null,
        public readonly ?int $maxDuration = null,
    ) {}

    /**
     * Fields this class does not know (newer OCPI versions, bilateral
     * extensions) are kept as extensions, so nothing received is lost.
     */
    public static function fromArray(array $data): self
    {
        $arguments = [];
        foreach (self::FIELDS as $field => $property) {
            if (isset($data[$field])) {
                $arguments[$property] = $data[$field];
            }
        }

        $extensions = array_diff_key($data, self::FIELDS);
        if ($extensions !== []) {
            $arguments['extensions'] = $extensions;
        }

        return new self(...$arguments);
    }

    public function toArray(): array
    {
        $data = [];
        foreach (self::FIELDS as $field => $property) {
            if ($this->{$property} !== null) {
                $data[$field] = $this->{$property};
            }
        }

        if ($this->extensions !== null) {
            $data = array_merge($data, $this->extensions);
        }

        return $data;
    }
}
