<?php

namespace Ocpi\Models\Locations;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ocpi\Support\Models\Model;

class LocationEvse extends Model
{
    use HasUuids,
        SoftDeletes;

    protected $primaryKey = 'emsp_id';

    protected $fillable = [
        'location_emsp_id',
        'uid',
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

    public function connectors(): HasMany
    {
        return $this->hasMany(LocationConnector::class, 'location_evse_emsp_id', 'emsp_id');
    }

    public function connectorsWithTrashed(): HasMany
    {
        return $this->hasMany(LocationConnector::class, 'location_evse_emsp_id', 'emsp_id')
            ->withTrashed();
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_emsp_id', 'emsp_id');
    }

    public function locationWithTrashed(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_emsp_id', 'emsp_id')
            ->withTrashed();
    }
}
