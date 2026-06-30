<?php

namespace Ocpi\Models\Locations;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ocpi\Support\Models\Model;

class LocationConnector extends Model
{
    use HasUuids,
        SoftDeletes;

    protected $primaryKey = 'emsp_id';

    protected $fillable = [
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

    public function evse(): BelongsTo
    {
        return $this->belongsTo(LocationEvse::class, 'location_evse_emsp_id', 'emsp_id');
    }
}
