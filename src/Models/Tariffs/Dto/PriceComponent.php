<?php

namespace Ocpi\Models\Tariffs\Dto;

class PriceComponent
{
    public function __construct(
        public readonly string $type,
        public readonly float $price,
        public readonly int $stepSize,
        public readonly ?float $vat = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: (string) $data['type'],
            price: (float) $data['price'],
            stepSize: (int) ($data['step_size'] ?? 1),
            vat: isset($data['vat']) ? (float) $data['vat'] : null,
        );
    }

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
