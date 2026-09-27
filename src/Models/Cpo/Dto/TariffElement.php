<?php

namespace Ocpi\Models\Cpo\Dto;

class TariffElement
{
    /**
     * @param  PriceComponent[]  $priceComponents
     */
    public function __construct(
        public readonly array $priceComponents,
        public readonly ?TariffRestrictions $restrictions = null,
    ) {}

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
