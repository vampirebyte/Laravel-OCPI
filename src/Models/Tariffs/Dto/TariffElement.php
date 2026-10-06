<?php

namespace Ocpi\Models\Tariffs\Dto;

class TariffElement
{
    /**
     * @param  PriceComponent[]  $priceComponents
     */
    public function __construct(
        public readonly array $priceComponents,
        public readonly ?TariffRestrictions $restrictions = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $priceComponents = [];
        foreach ($data['price_components'] ?? [] as $priceComponent) {
            $priceComponents[] = PriceComponent::fromArray($priceComponent);
        }

        return new self(
            priceComponents: $priceComponents,
            restrictions: isset($data['restrictions']) ? TariffRestrictions::fromArray($data['restrictions']) : null,
        );
    }

    public function toArray(): array
    {
        $priceComponents = [];
        foreach ($this->priceComponents as $priceComponent) {
            $priceComponents[] = $priceComponent->toArray();
        }

        $data = [
            'price_components' => $priceComponents,
        ];

        if ($this->restrictions !== null) {
            $data['restrictions'] = $this->restrictions->toArray();
        }

        return $data;
    }
}
