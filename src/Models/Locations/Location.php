<?php

namespace Ocpi\Models\Locations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasVersion7Uuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ocpi\Models\PartyRole;
use Ocpi\Support\Models\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Location extends Model
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

    public function evses(): HasMany
    {
        return $this->hasMany(LocationEvse::class, 'location_emsp_id', 'emsp_id');
    }

    public function evsesWithTrashed(): HasMany
    {
        return $this->hasMany(LocationEvse::class, 'location_emsp_id', 'emsp_id')
            ->withTrashed();
    }

    public function party_role(): BelongsTo
    {
        return $this->belongsTo(PartyRole::class);
    }
}
