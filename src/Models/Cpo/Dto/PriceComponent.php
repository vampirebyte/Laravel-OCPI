<?php

namespace Ocpi\Models\Cpo\Dto;

class PriceComponent
{
    public function __construct(
        public readonly string $type,
        public readonly float $price,
        public readonly int $stepSize,
        public readonly ?float $vat = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'type' => $this->type,
            'price' => $this->price,
            'step_size' => $this->stepSize,
        ];

        if ($this->vat !== null) {
            $data['vat'] = $this->vat;
        }

        return $data;
    }
}
