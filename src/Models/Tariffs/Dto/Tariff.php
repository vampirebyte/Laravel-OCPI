<?php

namespace Ocpi\Models\Tariffs\Dto;

use Ocpi\Models\Cpo\Contracts\OcpiObject;
use Ocpi\Models\Cpo\Dto\Price;

/**
 * OCPI Tariff object, shared by both roles: the CPO builds it from its own
 * tariffs to push them, the eMSP reads the tariffs it received into it.
 */
class Tariff implements OcpiObject
{
    /**
     * @param  TariffElement[]  $elements
     * @param  string|null  $type  2.2+ TariffType, e.g. REGULAR or AD_HOC_PAYMENT
     * @param  Price|null  $maxPrice  2.2+: a session never costs more - card terminals block it on the card
     * @param  array<int, array{language: string, text: string}>  $altText  the tariff in words (tariff_alt_text)
     */
    public function __construct(
        public readonly string $id,
        public readonly string $countryCode,
        public readonly string $partyId,
        public readonly string $currency,
        public readonly array $elements,
        public readonly string $lastUpdated,
        public readonly ?string $type = null,
        public readonly ?Price $maxPrice = null,
        public readonly array $altText = [],
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

        $maxPrice = null;
        if (isset($data['max_price']['excl_vat'])) {
            $inclVat = isset($data['max_price']['incl_vat']) ? (float) $data['max_price']['incl_vat'] : null;
            $maxPrice = new Price((float) $data['max_price']['excl_vat'], $inclVat);
        }

        return new self(
            id: (string) $data['id'],
            countryCode: (string) ($data['country_code'] ?? $countryCode),
            partyId: (string) ($data['party_id'] ?? $partyId),
            currency: (string) $data['currency'],
            elements: $elements,
            lastUpdated: (string) $data['last_updated'],
            type: $data['type'] ?? null,
            maxPrice: $maxPrice,
            altText: $data['tariff_alt_text'] ?? [],
        );
    }

    /**
     * In the shape of the version asked for: 2.1.1 tariffs have no
     * country_code / party_id, type or max_price.
     */
    public function toArray(string $version = '2.2.1'): array
    {
        $elements = [];
        foreach ($this->elements as $element) {
            $elements[] = $element->toArray($version);
        }

        if (version_compare($version, '2.2', '<')) {
            $tariff = [
                'id' => $this->id,
                'currency' => $this->currency,
            ];
            if ($this->altText !== []) {
                $tariff['tariff_alt_text'] = $this->altText;
            }
            $tariff['elements'] = $elements;
            $tariff['last_updated'] = $this->lastUpdated;

            return $tariff;
        }

        $tariff = [
            'country_code' => $this->countryCode,
            'party_id' => $this->partyId,
            'id' => $this->id,
            'currency' => $this->currency,
        ];
        if ($this->type !== null) {
            $tariff['type'] = $this->type;
        }
        if ($this->altText !== []) {
            $tariff['tariff_alt_text'] = $this->altText;
        }
        $tariff['elements'] = $elements;
        if ($this->maxPrice !== null) {
            $tariff['max_price'] = $this->maxPrice->toArray();
        }
        $tariff['last_updated'] = $this->lastUpdated;

        return $tariff;
    }
}
