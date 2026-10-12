<?php

namespace Ocpi\Models\Cpo\Dto;

use Ocpi\Models\Cpo\Contracts\OcpiObject;
use Ocpi\Support\Payload;

/**
 * An OCPI Session, serialized in the shape of the version asked for:
 * - 2.1.1: start_datetime / end_datetime, auth_id, the full `location` (with
 *   only the EVSE and connector used), a number as total_cost (excl. VAT);
 * - 2.2.1: country_code / party_id, start_date_time / end_date_time,
 *   cdr_token, authorization_reference, location_id / evse_uid /
 *   connector_id, a Price as total_cost.
 */
class Session implements OcpiObject
{
    /**
     * @param  string  $countryCode  ours, the CPO's
     * @param  string  $partyId  ours, the CPO's
     * @param  string  $authMethod  AUTH_REQUEST, COMMAND or WHITELIST
     * @param  Location  $location  with only the EVSE and connector of the session
     * @param  ChargingPeriod[]  $chargingPeriods
     * @param  string  $status  ACTIVE, COMPLETED, INVALID, PENDING or RESERVATION
     */
    public function __construct(
        public readonly string $id,
        public readonly string $countryCode,
        public readonly string $partyId,
        public readonly string $startDateTime,
        public readonly ?string $endDateTime,
        public readonly float $kwh,
        public readonly CdrToken $token,
        public readonly string $authMethod,
        public readonly Location $location,
        public readonly string $evseUid,
        public readonly string $connectorId,
        public readonly string $currency,
        public readonly string $status,
        public readonly string $lastUpdated,
        public readonly ?string $authorizationReference = null,
        public readonly array $chargingPeriods = [],
        public readonly ?Price $totalCost = null,
        public readonly ?string $meterId = null,
    ) {}

    public function toArray(string $version): array
    {
        $chargingPeriods = [];
        foreach ($this->chargingPeriods as $chargingPeriod) {
            $chargingPeriods[] = $chargingPeriod->toArray($version);
        }

        if (version_compare($version, '2.2', '<')) {
            return Payload::withoutNulls([
                'id' => $this->id,
                'start_datetime' => $this->startDateTime,
                'end_datetime' => $this->endDateTime,
                'kwh' => round($this->kwh, 4),
                'auth_id' => $this->token->contractId,
                // 2.1.1 has no COMMAND: a session started by a command was authorized by request
                'auth_method' => $this->authMethod === 'COMMAND' ? 'AUTH_REQUEST' : $this->authMethod,
                'location' => $this->location->toArray($version),
                'meter_id' => $this->meterId,
                'currency' => $this->currency,
                'charging_periods' => $chargingPeriods ?: null,
                'total_cost' => $this->totalCost !== null ? round($this->totalCost->exclVat, 4) : null,
                // 2.1.1 has no RESERVATION status
                'status' => $this->status === 'RESERVATION' ? 'PENDING' : $this->status,
                'last_updated' => $this->lastUpdated,
            ]);
        }

        return Payload::withoutNulls([
            'country_code' => $this->countryCode,
            'party_id' => $this->partyId,
            'id' => $this->id,
            'start_date_time' => $this->startDateTime,
            'end_date_time' => $this->endDateTime,
            'kwh' => round($this->kwh, 4),
            'cdr_token' => $this->token->toArray(),
            'auth_method' => $this->authMethod,
            'authorization_reference' => $this->authorizationReference,
            'location_id' => $this->location->id,
            'evse_uid' => $this->evseUid,
            'connector_id' => $this->connectorId,
            'meter_id' => $this->meterId,
            'currency' => $this->currency,
            'charging_periods' => $chargingPeriods ?: null,
            'total_cost' => $this->totalCost?->toArray(),
            'status' => $this->status,
            'last_updated' => $this->lastUpdated,
        ]);
    }
}
