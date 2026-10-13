<?php

namespace Ocpi\Models\Payments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Ocpi\Models\Party;
use Ocpi\Models\Payments\Enums\InvoiceCreator;
use Ocpi\Support\Models\Model;

/**
 * A payment terminal of a PTP (payments module): the Terminal object as the
 * PTP sent it, with the locations / EVSEs we, the CPO, assigned to it.
 * Without terminal_id it is our activation request, until the PTP creates
 * the terminal (POST terminals) with the same serial number.
 *
 * @property int $id
 * @property int $party_id the PTP
 * @property string|null $terminal_id the OCPI terminal id, set by the PTP
 * @property string|null $serial_number the terminal TID
 * @property array<string, mixed> $object the Terminal object
 * @property Carbon $last_updated
 * @property Carbon|null $activation_requested_at
 * @property Carbon|null $deactivated_at
 * @property-read Party $party
 */
class PaymentTerminal extends Model
{
    protected $fillable = [
        'party_id',
        'terminal_id',
        'serial_number',
        'object',
        'last_updated',
        'activation_requested_at',
        'deactivated_at',
    ];

    protected function casts(): array
    {
        return [
            'object' => 'array',
            'last_updated' => 'datetime',
            'activation_requested_at' => 'datetime',
            'deactivated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Party, $this>
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    /***
     * Scopes.
     ***/

    /**
     * The terminals the PTP created - not our activation requests still waiting for it.
     */
    public function scopeCreatedByPtp(Builder $query): void
    {
        $query->whereNotNull('terminal_id');
    }

    /**
     * The OCPI GET list filters: last_updated from date_from (inclusive) to date_to (exclusive).
     *
     * @param  array{date_from?: string, date_to?: string}  $filters
     */
    public function scopeLastUpdatedBetween(Builder $query, array $filters): void
    {
        if (isset($filters['date_from'])) {
            $query->where('last_updated', '>=', Carbon::parse($filters['date_from']));
        }
        if (isset($filters['date_to'])) {
            $query->where('last_updated', '<', Carbon::parse($filters['date_to']));
        }
    }

    /***
     * Methods.
     ***/

    /**
     * Our activation request, the PTP did not create the terminal yet.
     */
    public function isAwaitingActivation(): bool
    {
        return $this->terminal_id === null && $this->deactivated_at === null;
    }

    public function isDeactivated(): bool
    {
        return $this->deactivated_at !== null;
    }

    /**
     * @return array<int, string>
     */
    public function locationIds(): array
    {
        return array_values((array) ($this->object['location_ids'] ?? []));
    }

    /**
     * @return array<int, string>
     */
    public function evseUids(): array
    {
        return array_values((array) ($this->object['evse_uids'] ?? []));
    }

    public function invoiceCreator(): ?InvoiceCreator
    {
        return InvoiceCreator::tryFrom((string) ($this->object['invoice_creator'] ?? ''));
    }

    public function invoiceBaseUrl(): ?string
    {
        return $this->object['invoice_base_url'] ?? null;
    }

    /**
     * The Terminal object as OCPI shows it.
     *
     * @return array<string, mixed>
     */
    public function toOcpiArray(): array
    {
        return array_merge($this->object, [
            'terminal_id' => $this->terminal_id,
            'last_updated' => $this->last_updated->toIso8601ZuluString(),
        ]);
    }
}
