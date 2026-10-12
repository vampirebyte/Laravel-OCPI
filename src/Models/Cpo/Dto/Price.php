<?php

namespace Ocpi\Models\Cpo\Dto;

use Ocpi\Support\Payload;

/**
 * An amount: a 2.2.1 Price object {excl_vat, incl_vat}, a plain number in 2.1.1.
 */
class Price
{
    public function __construct(
        public readonly float $exclVat,
        public readonly ?float $inclVat = null,
    ) {}

    /**
     * @return array{excl_vat: float, incl_vat?: float}
     */
    public function toArray(): array
    {
        return Payload::withoutNulls([
            'excl_vat' => round($this->exclVat, 4),
            'incl_vat' => $this->inclVat !== null ? round($this->inclVat, 4) : null,
        ]);
    }
}
