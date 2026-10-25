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

    /**
     * `vat` exists from OCPI 2.2. A 2.1.1 price component has none, so its
     * price is what the driver pays - VAT included, like the 2.1.1 CDR's
     * total_cost (see Cdr::toArray).
     */
    public function toArray(string $version = '2.2.1'): array
    {
        if (version_compare($version, '2.2', '<')) {
            return [
                'type' => $this->type,
                'price' => $this->vat !== null ? round($this->price * (1 + $this->vat / 100), 4) : $this->price,
                'step_size' => $this->stepSize,
            ];
        }

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
