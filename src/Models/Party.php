<?php

namespace Ocpi\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ocpi\Support\Models\Model;

class Party extends Model
{
    use SoftDeletes;

    /**
     * The OCPI credentials tokens never leave in a serialized party - e.g. a
     * command DTO stored as JSON by the host application.
     */
    protected $hidden = [
        'server_token',
        'client_token',
    ];

    protected $fillable = [
        'code',
        'name',
        'server_token',
        'url',
        'version',
        'version_url',
        'endpoints',
        'client_token',
        'registered',
    ];

    protected function casts(): array
    {
        return [
            'endpoints' => AsArrayObject::class,
            'registered' => 'boolean',
        ];
    }

    /***
     * Computed Attributes.
     ***/

    protected function encodedClientToken(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => version_compare($this->version, '2.2', '>=')
                ? self::encodeToken($attributes['client_token'])
                : $attributes['client_token'],
        );
    }

    protected function encodedServerToken(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => version_compare($this->version, '2.2', '>=')
                ? self::encodeToken($attributes['server_token'])
                : $attributes['server_token'],
        );
    }

    /***
     * Scopes.
     ***/

    public function scopeRegistered(Builder $query, bool $registered = true): void
    {
        $query->where('registered', $registered);
    }

    /***
     * Relations.
     ***/

    public function roles(): HasMany
    {
        return $this->hasMany(PartyRole::class);
    }

    /***
     * Methods.
     ***/

    public function generateToken(): string
    {
        return $this->code.'_'.Str::uuid();
    }

    /**
     * Whether the party is ourselves - one of its roles is ours (see
     * PartyRole::isOurs). Reads the loaded roles: load them with the deleted
     * ones to tell it for a deleted party too.
     */
    public function isOurs(): bool
    {
        foreach ($this->roles as $role) {
            if ($role->isOurs()) {
                return true;
            }
        }

        return false;
    }

    public static function encodeToken(string $token): string
    {
        return base64_encode($token);
    }

    public static function decodeToken(string $token, ?Party $party = null): false|string
    {
        if ($party && version_compare($party->version, '2.2', '<')) {
            return $token;
        }

        return base64_decode($token, true);
    }
}
