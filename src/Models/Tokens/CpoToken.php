<?php

namespace Ocpi\Models\Tokens;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Ocpi\Models\Party;
use Ocpi\Models\Tokens\Dto\Token;
use Ocpi\Support\Models\Model;

/**
 * A token an eMSP pushed to us (Tokens receiver interface).
 *
 * @property int $id
 * @property int $party_id the eMSP that pushed it
 * @property string $country_code
 * @property string $party_code OCPI party_id of the issuing eMSP
 * @property string $uid
 * @property string $type
 * @property string $contract_id
 * @property string|null $visual_number
 * @property string $issuer
 * @property string|null $group_id
 * @property bool $valid
 * @property string $whitelist ALWAYS, ALLOWED, ALLOWED_OFFLINE or NEVER
 * @property string|null $language
 * @property Carbon $last_updated
 * @property array<string, mixed> $object the Token as last received
 * @property-read Party $party
 */
class CpoToken extends Model
{
    protected $fillable = [
        'party_id',
        'country_code',
        'party_code',
        'uid',
        'type',
        'contract_id',
        'visual_number',
        'issuer',
        'group_id',
        'valid',
        'whitelist',
        'language',
        'last_updated',
        'object',
    ];

    protected function casts(): array
    {
        return [
            'valid' => 'boolean',
            'last_updated' => 'datetime',
            'object' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Party, $this>
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function toDto(): Token
    {
        return new Token(
            countryCode: $this->country_code,
            partyId: $this->party_code,
            uid: $this->uid,
            type: $this->type,
            contractId: $this->contract_id,
            issuer: $this->issuer,
            valid: $this->valid,
            whitelist: $this->whitelist,
            lastUpdated: $this->last_updated->toIso8601ZuluString(),
            visualNumber: $this->visual_number,
            groupId: $this->group_id,
            language: $this->language,
        );
    }
}
