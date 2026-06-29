<?php

namespace Ocpi\Modules\Emsp\Sessions\Traits;

use Ocpi\Models\Sessions\Session;
use Ocpi\Modules\Emsp\Sessions\Events;

trait HandlesSession
{
    private function sessionSearch(string $session_id, int $party_role_id): ?Session
    {
        return Session::query()
            ->where('id', $session_id)
            ->where('party_role_id', $party_role_id)
            ->first();
    }

    private function sessionCreate(array $payload, int $party_role_id, string $session_id, ?string $location_evse_emsp_id): bool
    {
        if (($payload['id'] ?? null) === null || $payload['id'] !== $session_id) {
            return false;
        }

        $session = new Session;
        $session->fill([
            'party_role_id' => $party_role_id,
            'location_evse_emsp_id' => $location_evse_emsp_id,
            'id' => $session_id,
            'object' => $payload,
        ]);

        if (! $session->save()) {
            return false;
        }

        Events\SessionCreated::dispatch($party_role_id, $session->id, $payload);

        return true;
    }

    private function sessionReplace(array $payload, Session $session): bool
    {
        if (($payload['id'] ?? null) === null || $payload['id'] !== $session->id) {
            return false;
        }

        $session->object = $payload;

        if (! $session->save()) {
            return false;
        }

        Events\SessionReplaced::dispatch($session->party_role_id, $session->id, $payload);

        return true;
    }

    private function sessionObjectUpdate(array $payload, Session $session): bool
    {
        foreach ($payload as $field => $value) {
            $session->object[$field] = $value;
        }

        if (! $session->save()) {
            return false;
        }

        Events\SessionUpdated::dispatch($session->party_role_id, $session->id, $payload);

        return true;
    }
}
