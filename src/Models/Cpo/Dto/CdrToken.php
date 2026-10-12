<?php

namespace Ocpi\Models\Cpo\Dto;

/**
 * The token a session or CDR is charged to: the 2.2.1 cdr_token, the 2.1.1
 * auth_id (its contract id).
 */
class CdrToken
{
    public function __construct(
        public readonly string $uid,
        public readonly string $type,
        public readonly string $contractId,
        public readonly string $countryCode,
        public readonly string $partyId,
    ) {}

    /**
     * @return array{country_code: string, party_id: string, uid: string, type: string, contract_id: string}
     */
    public function toArray(): array
    {
        return [
            'country_code' => $this->countryCode,
            'party_id' => $this->partyId,
            'uid' => $this->uid,
            'type' => $this->type,
            'contract_id' => $this->contractId,
        ];
    }
}
