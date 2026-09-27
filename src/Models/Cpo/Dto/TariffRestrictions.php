<?php

namespace Ocpi\Models\Cpo\Dto;

class TariffRestrictions
{
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
    ) {}

    public function toArray(): array
    {
        $all = [
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'day_of_week' => $this->dayOfWeek,
        ];

        $data = [];
        foreach ($all as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        if ($this->extensions !== null) {
            $data = array_merge($data, $this->extensions);
        }

        return $data;
    }
}
