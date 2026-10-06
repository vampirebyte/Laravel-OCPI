<?php

namespace Ocpi\Models\Tariffs;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ocpi\Models\PartyRole;
use Ocpi\Models\Tariffs\Dto\Tariff as TariffDto;
use Ocpi\Support\Models\Model;

/**
 * A tariff received from a CPO (eMSP role) - a read-only copy of the OCPI
 * object, owned by the CPO that sent it. The tariffs this platform
 * publishes as a CPO live in the application, not here.
 */
class Tariff extends Model
{
    use HasUuids,
        SoftDeletes;

    protected $primaryKey = 'emsp_id';

    protected $fillable = [
        'party_role_id',
        'id',
        'object',
    ];

    protected function casts(): array
    {
        return [
            'object' => AsArrayObject::class,
        ];
    }

    public function toDto(): TariffDto
    {
        return TariffDto::fromArray(
            data: $this->object->getArrayCopy(),
            countryCode: $this->party_role?->country_code,
            partyId: $this->party_role?->code,
        );
    }

    /***
     * Scopes.
     ***/

    public function scopePartyRole(Builder $query, int $party_role_id): void
    {
        $query->where('party_role_id', $party_role_id);
    }

    /***
     * Relations.
     ***/

    public function party_role(): BelongsTo
    {
        return $this->belongsTo(PartyRole::class);
    }
}
