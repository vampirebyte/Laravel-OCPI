<?php

namespace Ocpi\Modules\Emsp\Tariffs\Traits;

use Ocpi\Models\Tariffs\Tariff;
use Ocpi\Modules\Emsp\Tariffs\Events;

trait HandlesTariff
{
    private function tariffSearch(int $party_role_id, string $tariff_id, bool $withTrashed = false): ?Tariff
    {
        return Tariff::query()
            ->when($withTrashed === true, function ($query) {
                return $query->withTrashed();
            })
            ->partyRole($party_role_id)
            ->where('id', $tariff_id)
            ->first();
    }

    private function tariffCreate(array $payload, int $party_role_id, string $tariff_id): bool
    {
        if (($payload['id'] ?? null) === null || $payload['id'] !== $tariff_id) {
            return false;
        }

        $tariff = new Tariff;
        $tariff->fill([
            'party_role_id' => $party_role_id,
            'id' => $tariff_id,
        ]);
        $tariff->object = $payload;
        $tariff->save();

        Events\TariffCreated::dispatch($party_role_id, $tariff_id, $payload);

        return true;
    }

    private function tariffReplace(array $payload, Tariff $tariff): bool
    {
        if (($payload['id'] ?? null) === null || $payload['id'] !== $tariff->id) {
            return false;
        }

        if ($tariff->trashed()) {
            $tariff->restore();
        }

        $tariff->object = $payload;
        $tariff->save();

        Events\TariffReplaced::dispatch($tariff->party_role_id, $tariff->id, $payload);

        return true;
    }

    private function tariffObjectUpdate(array $payload, Tariff $tariff): bool
    {
        foreach ($payload as $field => $value) {
            $tariff->object[$field] = $value;
        }

        if (! $tariff->save()) {
            return false;
        }

        Events\TariffUpdated::dispatch($tariff->party_role_id, $tariff->id, $payload);

        return true;
    }

    private function tariffRemove(Tariff $tariff): bool
    {
        if (! $tariff->delete()) {
            return false;
        }

        Events\TariffRemoved::dispatch($tariff->party_role_id, $tariff->id);

        return true;
    }
}
