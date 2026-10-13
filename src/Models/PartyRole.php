<?php

namespace Ocpi\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ocpi\Support\Models\Model;

class PartyRole extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'role',
        'country_code',
        'business_details',
    ];

    protected function casts(): array
    {
        return [
            'business_details' => AsArrayObject::class,
        ];
    }

    /***
     * Scopes.
     ***/

    public function scopeCode(Builder $query, string $code): void
    {
        $query->where('code', $code);
    }

    public function scopeCountryCode(Builder $query, string $countryCode): void
    {
        $query->where('country_code', $countryCode);
    }

    /***
     * Relations.
     ***/

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    /***
     * Methods.
     ***/

    /**
     * Whether this role is one of our own - our CPO or our EMSP, as set in
     * ocpi-cpo.party / ocpi-emsp.party: we are connected to ourselves, e.g.
     * our EMSP side to our CPO side.
     */
    public function isOurs(): bool
    {
        $ourParty = match ($this->role) {
            'CPO' => config('ocpi-cpo.party'),
            'EMSP' => config('ocpi-emsp.party'),
            default => null,
        };

        if (empty($ourParty['party_id']) || empty($ourParty['country_code'])) {
            return false;
        }

        return strtoupper((string) $this->code) === strtoupper((string) $ourParty['party_id'])
            && strtoupper((string) $this->country_code) === strtoupper((string) $ourParty['country_code']);
    }
}
