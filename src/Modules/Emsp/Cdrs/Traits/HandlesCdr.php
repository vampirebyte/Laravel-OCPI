<?php

namespace Ocpi\Modules\Emsp\Cdrs\Traits;

use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Ocpi\Models\Cdrs\Cdr;
use Ocpi\Modules\Emsp\Cdrs\Events;

trait HandlesCdr
{
    private function cdrSearch(?string $cdr_emsp_id = null, ?string $cdr_id = null, ?int $party_role_id = null): ?Cdr
    {
        return Cdr::query()
            ->when(
                $cdr_emsp_id !== null,
                function ($query) use ($cdr_emsp_id) {
                    $query->where('emsp_id', $cdr_emsp_id);
                },
                function ($query) use ($cdr_id) {
                    $query->where('id', $cdr_id);
                })
            ->when(
                $party_role_id !== null,
                function ($query) use ($party_role_id) {
                    $query->where('party_role_id', $party_role_id);
                })
            ->first();
    }

    private function cdrCreate(array $payload, int $party_role_id, ?string $location_evse_emsp_id): ?Cdr
    {
        if (($payload['id'] ?? null) === null) {
            return null;
        }

        $cdr = new Cdr;
        $cdr->fill([
            'party_role_id' => $party_role_id,
            'location_evse_emsp_id' => $location_evse_emsp_id,
            'id' => $payload['id'],
            'object' => $payload,
        ]);

        if (! $cdr->save()) {
            return null;
        }

        Events\CdrCreated::dispatch($party_role_id, $cdr->id, $payload);

        return $cdr;
    }

    private function cdrRoute(Cdr $cdr): string
    {
        return route(
            config('ocpi.server.routing.name_prefix')
            .Str::replace('.', '_', Context::get('ocpi_version'))
            .'.cdrs', [
                'cdr_emsp_id' => $cdr->emsp_id,
            ]
        );
    }
}
