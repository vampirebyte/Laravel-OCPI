<?php

namespace Ocpi\Models\Cpo\Dto;

use Ocpi\Support\Payload;

/**
 * An OCPI Location, serialized in the shape of the version asked for:
 * - 2.1.1 has a location `type` and no country_code / party_id / publish;
 * - 2.2.1 has country_code, party_id, publish, a required `time_zone` and an
 *   optional `parking_type` (same values as the 2.1.1 type, where they exist).
 */
class Location
{
    /**
     * 2.1.1 LocationType values that are also 2.2.1 ParkingType values.
     */
    private const PARKING_TYPES = ['ON_STREET', 'PARKING_GARAGE', 'UNDERGROUND_GARAGE', 'PARKING_LOT'];

    /**
     * @param  Evse[]  $evses
     * @param  string  $type  2.1.1 LocationType: ON_STREET, PARKING_GARAGE, UNDERGROUND_GARAGE, PARKING_LOT, OTHER, UNKNOWN
     * @param  string|null  $timeZone  IANA tz, e.g. "Europe/Bucharest" - required in 2.2.1, the app time zone when missing
     */
    public function __construct(
        public readonly string $id,
        public readonly string $countryCode,
        public readonly string $partyId,
        public readonly string $type,
        public readonly ?string $name,
        public readonly string $address,
        public readonly string $city,
        public readonly ?string $postalCode,
        public readonly string $country,
        public readonly Coordinates $coordinates,
        public readonly array $evses,
        public readonly string $lastUpdated,
        public readonly ?string $timeZone = null,
        public readonly bool $publish = true,
    ) {}

    public function toArray(string $version): array
    {
        $evses = [];
        foreach ($this->evses as $evse) {
            $evses[] = $evse->toArray($version);
        }

        if (version_compare($version, '2.2', '<')) {
            return Payload::withoutNulls([
                'id' => $this->id,
                'type' => $this->type ?: 'UNKNOWN',
                'name' => $this->name,
                'address' => $this->address,
                'city' => $this->city,
                'postal_code' => $this->postalCode,
                'country' => $this->country,
                'coordinates' => $this->coordinates->toArray(),
                'evses' => $evses,
                'time_zone' => $this->timeZone,
                'last_updated' => $this->lastUpdated,
            ]);
        }

        return Payload::withoutNulls([
            'country_code' => $this->countryCode,
            'party_id' => $this->partyId,
            'id' => $this->id,
            'publish' => $this->publish,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
            'coordinates' => $this->coordinates->toArray(),
            'parking_type' => in_array($this->type, self::PARKING_TYPES, true) ? $this->type : null,
            'evses' => $evses,
            'time_zone' => $this->timeZone ?? config('app.timezone'),
            'last_updated' => $this->lastUpdated,
        ]);
    }
}
