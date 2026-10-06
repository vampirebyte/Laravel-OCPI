<?php

namespace Ocpi\Models\Tariffs\Dto;

/**
 * OCPI Tariff object, shared by both roles: the CPO builds it from its own
 * tariffs to push them, the eMSP reads the tariffs it received into it.
 */
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

    /**
     * OCPI 2.1.1 tariffs carry no country_code / party_id - the caller passes
     * the ones of the party that sent it.
     */
    public static function fromArray(array $data, ?string $countryCode = null, ?string $partyId = null): self
    {
        $elements = [];
        foreach ($data['elements'] ?? [] as $element) {
            $elements[] = TariffElement::fromArray($element);
        }

        return new self(
            id: (string) $data['id'],
            countryCode: (string) ($data['country_code'] ?? $countryCode),
            partyId: (string) ($data['party_id'] ?? $partyId),
            currency: (string) $data['currency'],
            elements: $elements,
            lastUpdated: (string) $data['last_updated'],
        );
    }

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
