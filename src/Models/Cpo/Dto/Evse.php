<?php

namespace Ocpi\Models\Cpo\Dto;

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

        $data = [
            'uid' => $this->uid,
            'evse_id' => $this->evseId,
            'status' => $this->status,
            'connectors' => $connectors,
            'last_updated' => $this->lastUpdated,
        ];

        if ($this->physicalReference !== null) {
            $data['physical_reference'] = $this->physicalReference;
        }

        return $data;
    }
}
