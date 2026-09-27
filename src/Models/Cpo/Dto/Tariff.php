<?php

namespace Ocpi\Models\Cpo\Dto;

class Tariff
{
    /**
     * @param  TariffElement[]  $elements
     */
    public function __construct(
        public readonly string $id,
        public readonly string $countryCode,
        public readonly string $partyId,
        public readonly string $currency,
        public readonly array $elements,
        public readonly string $lastUpdated,
    ) {}

    public function toArray(): array
    {
        $elements = [];
        foreach ($this->elements as $element) {
            $elements[] = $element->toArray();
        }

        return [
            'country_code' => $this->countryCode,
            'party_id' => $this->partyId,
            'id' => $this->id,
            'currency' => $this->currency,
            'elements' => $elements,
            'last_updated' => $this->lastUpdated,
        ];
    }
}
