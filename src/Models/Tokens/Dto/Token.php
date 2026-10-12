<?php

namespace Ocpi\Models\Tokens\Dto;

use Ocpi\Support\Payload;

/**
 * An OCPI Token, read from and serialized for either version:
 * - 2.1.1: auth_id, no country_code / party_id / group_id;
 * - 2.2.1: country_code, party_id, contract_id, group_id.
 */
class Token
{
    public function __construct(
        public readonly string $countryCode,
        public readonly string $partyId,
        public readonly string $uid,
        public readonly string $type,
        public readonly string $contractId,
        public readonly string $issuer,
        public readonly bool $valid,
        public readonly string $whitelist,
        public readonly string $lastUpdated,
        public readonly ?string $visualNumber = null,
        public readonly ?string $groupId = null,
        public readonly ?string $language = null,
    ) {}

    /**
     * A 2.1.1 token carries no country_code / party_id - the ones from the URL are used.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, string $countryCode, string $partyId): self
    {
        return new self(
            countryCode: (string) ($data['country_code'] ?? $countryCode),
            partyId: (string) ($data['party_id'] ?? $partyId),
            uid: (string) $data['uid'],
            type: (string) ($data['type'] ?? 'RFID'),
            contractId: (string) ($data['contract_id'] ?? $data['auth_id']),
            issuer: (string) $data['issuer'],
            valid: (bool) $data['valid'],
            whitelist: (string) $data['whitelist'],
            lastUpdated: (string) $data['last_updated'],
            visualNumber: isset($data['visual_number']) ? (string) $data['visual_number'] : null,
            groupId: isset($data['group_id']) ? (string) $data['group_id'] : null,
            language: isset($data['language']) ? (string) $data['language'] : null,
        );
    }

    public function toArray(string $version): array
    {
        if (version_compare($version, '2.2', '<')) {
            return Payload::withoutNulls([
                'uid' => $this->uid,
                'type' => $this->type,
                'auth_id' => $this->contractId,
                'visual_number' => $this->visualNumber,
                'issuer' => $this->issuer,
                'valid' => $this->valid,
                'whitelist' => $this->whitelist,
                'language' => $this->language,
                'last_updated' => $this->lastUpdated,
            ]);
        }

        return Payload::withoutNulls([
            'country_code' => $this->countryCode,
            'party_id' => $this->partyId,
            'uid' => $this->uid,
            'type' => $this->type,
            'contract_id' => $this->contractId,
            'visual_number' => $this->visualNumber,
            'issuer' => $this->issuer,
            'group_id' => $this->groupId,
            'valid' => $this->valid,
            'whitelist' => $this->whitelist,
            'language' => $this->language,
            'last_updated' => $this->lastUpdated,
        ]);
    }
}
