<?php

namespace Ocpi\Modules\Cpo\Payments\Client;

use Ocpi\Support\Client\Resource as OcpiResource;

/**
 * The PTP's payments module (sender interface), called by us, the CPO.
 */
class Resource extends OcpiResource
{
    /**
     * Every terminal of the PTP, all pages.
     *
     * @param  array{date_from?: string, date_to?: string}  $query
     * @return array<int, array<string, mixed>>
     */
    public function terminals(array $query = []): array
    {
        return $this->requestGetAllPagesSend(endpoint: 'terminals', query: $query);
    }

    public function terminal(string $terminalId): ?array
    {
        return $this->requestGetSend(endpoint: "terminals/{$terminalId}");
    }

    /**
     * Ask the PTP to create a terminal - it answers by creating it on our side (POST terminals).
     */
    public function activate(array $payload): array|string|null
    {
        return $this->requestPostSend(payload: $payload, endpoint: 'terminals/activate');
    }

    public function deactivate(string $terminalId): array|string|null
    {
        return $this->requestPostSend(
            payload: ['terminal_id' => $terminalId],
            endpoint: "terminals/{$terminalId}/deactivate",
        );
    }

    /**
     * Assign locations / EVSEs to a terminal: location_ids, evse_uids, last_updated.
     */
    public function patch(string $terminalId, array $payload): array|string|null
    {
        return $this->requestPatchSend(payload: $payload, endpoint: "terminals/{$terminalId}");
    }

    public function put(string $terminalId, array $payload): array|string|null
    {
        return $this->requestPutSend(payload: $payload, endpoint: "terminals/{$terminalId}");
    }

    /**
     * Every financial advice confirmation of the PTP, all pages.
     *
     * @param  array{date_from?: string, date_to?: string}  $query
     * @return array<int, array<string, mixed>>
     */
    public function financialAdviceConfirmations(array $query = []): array
    {
        return $this->requestGetAllPagesSend(endpoint: 'financial-advice-confirmations', query: $query);
    }
}
