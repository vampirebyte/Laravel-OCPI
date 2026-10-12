<?php

namespace Ocpi\Models\Cpo\Dto;

use Ocpi\Models\Cpo\Contracts\OcpiObject;
use Ocpi\Models\Tariffs\Dto\Tariff;
use Ocpi\Support\Payload;

/**
 * An OCPI CDR, serialized in the shape of the version asked for:
 * - 2.1.1: stop_date_time, auth_id, the full `location` (with only the EVSE
 *   and connector used), a number as total_cost (what the driver pays);
 * - 2.2.1: country_code / party_id, end_date_time, session_id, cdr_token,
 *   cdr_location, Price objects as costs.
 */
class Cdr implements OcpiObject
{
    /**
     * @param  string  $countryCode  ours, the CPO's
     * @param  string  $partyId  ours, the CPO's
     * @param  Location  $location  with only the EVSE and connector of the session
     * @param  ChargingPeriod[]  $chargingPeriods  at least one
     * @param  Tariff[]  $tariffs  the tariffs that applied
     * @param  float  $totalEnergy  kWh
     * @param  float  $totalTime  hours
     * @param  float|null  $totalParkingTime  hours
     */
    public function __construct(
        public readonly string $id,
        public readonly string $countryCode,
        public readonly string $partyId,
        public readonly string $startDateTime,
        public readonly string $endDateTime,
        public readonly CdrToken $token,
        public readonly string $authMethod,
        public readonly Location $location,
        public readonly string $currency,
        public readonly array $chargingPeriods,
        public readonly Price $totalCost,
        public readonly float $totalEnergy,
        public readonly float $totalTime,
        public readonly string $lastUpdated,
        public readonly ?string $sessionId = null,
        public readonly ?string $authorizationReference = null,
        public readonly array $tariffs = [],
        public readonly ?float $totalParkingTime = null,
        public readonly ?Price $totalFixedCost = null,
        public readonly ?Price $totalEnergyCost = null,
        public readonly ?Price $totalTimeCost = null,
        public readonly ?Price $totalParkingCost = null,
        public readonly ?string $meterId = null,
    ) {}

    public function toArray(string $version): array
    {
        $chargingPeriods = [];
        foreach ($this->chargingPeriods as $chargingPeriod) {
            $chargingPeriods[] = $chargingPeriod->toArray($version);
        }

        $tariffs = [];
        foreach ($this->tariffs as $tariff) {
            $tariffs[] = $tariff->toArray($version);
        }

        if (version_compare($version, '2.2', '<')) {
            return Payload::withoutNulls([
                'id' => $this->id,
                'start_date_time' => $this->startDateTime,
                'stop_date_time' => $this->endDateTime,
                'auth_id' => $this->token->contractId,
                // 2.1.1 has no COMMAND: a session started by a command was authorized by request
                'auth_method' => $this->authMethod === 'COMMAND' ? 'AUTH_REQUEST' : $this->authMethod,
                'location' => $this->location->toArray($version),
                'meter_id' => $this->meterId,
                'currency' => $this->currency,
                'tariffs' => $tariffs ?: null,
                'charging_periods' => $chargingPeriods,
                // 2.1.1 tariffs have no VAT: the total is what the driver pays
                'total_cost' => round($this->totalCost->inclVat ?? $this->totalCost->exclVat, 4),
                'total_energy' => round($this->totalEnergy, 4),
                'total_time' => round($this->totalTime, 4),
                'total_parking_time' => $this->totalParkingTime !== null ? round($this->totalParkingTime, 4) : null,
                'last_updated' => $this->lastUpdated,
            ]);
        }

        return Payload::withoutNulls([
            'country_code' => $this->countryCode,
            'party_id' => $this->partyId,
            'id' => $this->id,
            'start_date_time' => $this->startDateTime,
            'end_date_time' => $this->endDateTime,
            'session_id' => $this->sessionId,
            'cdr_token' => $this->token->toArray(),
            'auth_method' => $this->authMethod,
            'authorization_reference' => $this->authorizationReference,
            'cdr_location' => $this->cdrLocation(),
            'meter_id' => $this->meterId,
            'currency' => $this->currency,
            'tariffs' => $tariffs ?: null,
            'charging_periods' => $chargingPeriods,
            'total_cost' => $this->totalCost->toArray(),
            'total_fixed_cost' => $this->totalFixedCost?->toArray(),
            'total_energy' => round($this->totalEnergy, 4),
            'total_energy_cost' => $this->totalEnergyCost?->toArray(),
            'total_time' => round($this->totalTime, 4),
            'total_time_cost' => $this->totalTimeCost?->toArray(),
            'total_parking_time' => $this->totalParkingTime !== null ? round($this->totalParkingTime, 4) : null,
            'total_parking_cost' => $this->totalParkingCost?->toArray(),
            'last_updated' => $this->lastUpdated,
        ]);
    }

    /**
     * The 2.2.1 CdrLocation: the location with the EVSE and connector used,
     * flattened.
     *
     * @return array<string, mixed>
     */
    private function cdrLocation(): array
    {
        $evse = $this->location->evses[0];
        $connector = $evse->connectors[0];

        return Payload::withoutNulls([
            'id' => $this->location->id,
            'name' => $this->location->name,
            'address' => $this->location->address,
            'city' => $this->location->city,
            'postal_code' => $this->location->postalCode,
            'country' => $this->location->country,
            'coordinates' => $this->location->coordinates->toArray(),
            'evse_uid' => $evse->uid,
            'evse_id' => $evse->evseId,
            'connector_id' => $connector->id,
            'connector_standard' => $connector->standard,
            'connector_format' => $connector->format,
            'connector_power_type' => $connector->powerType,
        ]);
    }
}
