<?php

namespace Ocpi\Models\Cpo\Dto;

use Ocpi\Support\Payload;

/**
 * An OCPI EVSE - the same fields in 2.1.1 and 2.2.1. `status` is an OCPI
 * Status: AVAILABLE, BLOCKED, CHARGING, INOPERATIVE, OUTOFORDER, PLANNED,
 * REMOVED, RESERVED or UNKNOWN.
 */
class Evse
{
    /**
     * @param  Connector[]  $connectors
     */
    public function __construct(
        public readonly string $uid,
        public readonly string $evseId,
        public readonly string $status,
        public readonly array $connectors,
        public readonly ?string $lastUpdated = null,
        public readonly ?string $physicalReference = null,
    ) {}

    public function toArray(string $version): array
    {
        $connectors = [];
        foreach ($this->connectors as $connector) {
            $connectors[] = $connector->toArray($version);
        }

        return Payload::withoutNulls([
            'uid' => $this->uid,
            'evse_id' => $this->evseId,
            'status' => $this->status,
            'connectors' => $connectors,
            'physical_reference' => $this->physicalReference,
            // required - an EVSE that never changed is as recent as now
            'last_updated' => $this->lastUpdated ?? now()->toIso8601ZuluString(),
        ]);
    }
}
