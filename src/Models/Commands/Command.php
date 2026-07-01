<?php

namespace Ocpi\Models\Commands;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ocpi\Models\Commands\Enums\CommandResponseType;
use Ocpi\Models\Commands\Enums\CommandResultType;
use Ocpi\Models\Commands\Enums\CommandType;
use Ocpi\Models\PartyRole;
use Ocpi\Support\Models\Model;

class Command extends Model
{
    use HasUlids;

    protected $fillable = [
        'party_role_id',
        'id',
        'type',
        'payload',
        'response',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'type' => CommandType::class,
            'payload' => AsArrayObject::class,
            'response' => CommandResponseType::class,
            'result' => CommandResultType::class,
        ];
    }

    /***
     * Relations.
     ***/

    public function party_role(): BelongsTo
    {
        return $this->belongsTo(PartyRole::class);
    }
}
