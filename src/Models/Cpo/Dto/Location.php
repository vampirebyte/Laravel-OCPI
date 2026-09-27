<?php

namespace Ocpi\Models\Cpo\Dto;

class Location
{
    /**
     * @param  Evse[]  $evses
     */
    public function __construct(
        public readonly string $id,
        public readonly string $countryCode,
        public readonly string $partyId,
        public readonly string $type,
        public readonly string $name,
        public readonly string $address,
        public readonly string $city,
        public readonly string $postalCode,
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

        $data = [
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
            'evses' => $evses,
            'last_updated' => $this->lastUpdated,
        ];

        if ($this->timeZone !== null) {
            $data['time_zone'] = $this->timeZone;
        }

        return $data;
    }
}
