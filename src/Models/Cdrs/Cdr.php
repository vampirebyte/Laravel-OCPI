<?php

namespace Ocpi\Models\Cdrs;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ocpi\Models\Locations\LocationEvse;
use Ocpi\Models\PartyRole;
use Ocpi\Support\Models\Model;

class Cdr extends Model
{
    use HasUuids,
        SoftDeletes;

    protected $primaryKey = 'emsp_id';

    protected $fillable = [
        'party_role_id',
        'location_evse_emsp_id',
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
     * Relations.
     ***/

    public function location_evse(): BelongsTo
    {
        return $this->belongsTo(LocationEvse::class, 'location_evse_emsp_id', 'emsp_id');
    }

    public function party_role(): BelongsTo
    {
        return $this->belongsTo(PartyRole::class);
    }
}
