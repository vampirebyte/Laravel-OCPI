<?php

namespace Ocpi\Models\Payments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Ocpi\Models\Party;
use Ocpi\Models\Payments\Enums\CaptureStatusCode;
use Ocpi\Support\Models\Model;
use Ocpi\Support\Payload;

/**
 * What a PTP captured on the card for a session (payments module), found by
 * the authorization_reference the PTP gave the session in START_SESSION. It
 * carries the EFT data the invoice of the session needs.
 *
 * @property int $id
 * @property int $party_id the PTP
 * @property string $confirmation_id the OCPI financial advice confirmation id
 * @property string $authorization_reference
 * @property string $currency
 * @property string $total_cost_excl_vat
 * @property string|null $total_cost_incl_vat
 * @property CaptureStatusCode $capture_status_code
 * @property string|null $capture_status_message
 * @property array<int, string>|null $eft_data
 * @property Carbon $last_updated
 * @property-read Party $party
 */
class FinancialAdviceConfirmation extends Model
{
    protected $fillable = [
        'party_id',
        'confirmation_id',
        'authorization_reference',
        'currency',
        'total_cost_excl_vat',
        'total_cost_incl_vat',
        'capture_status_code',
        'capture_status_message',
        'eft_data',
        'last_updated',
    ];

    protected function casts(): array
    {
        return [
            'total_cost_excl_vat' => 'decimal:4',
            'total_cost_incl_vat' => 'decimal:4',
            'capture_status_code' => CaptureStatusCode::class,
            'eft_data' => 'array',
            'last_updated' => 'datetime',
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
     * Money was taken from the card - all of it, or part of it.
     */
    public function isCaptured(): bool
    {
        return $this->capture_status_code !== CaptureStatusCode::FAILED;
    }

    /**
     * What was captured: incl. VAT when known.
     */
    public function totalCost(): float
    {
        return (float) ($this->total_cost_incl_vat ?? $this->total_cost_excl_vat);
    }

    /**
     * The card brand: the first EFT line that is not a "label: value" line,
     * e.g. "Mastercard". EFT data is free text - null when there is none.
     */
    public function cardBrand(): ?string
    {
        foreach ($this->eft_data ?? [] as $eftLine) {
            if (! str_contains($eftLine, ':')) {
                return $eftLine;
            }
        }

        return null;
    }

    /**
     * The last 4 digits of the masked card number, e.g. "1234" from
     * "Nr: **** **** **** 1234" - null when no EFT line has them.
     */
    public function cardLastDigits(): ?string
    {
        foreach ($this->eft_data ?? [] as $eftLine) {
            $lastCharacters = substr(trim($eftLine), -4);

            if (str_contains($eftLine, '*') && ctype_digit($lastCharacters)) {
                return $lastCharacters;
            }
        }

        return null;
    }

    /**
     * The FinancialAdviceConfirmation object as OCPI shows it.
     *
     * @return array<string, mixed>
     */
    public function toOcpiArray(): array
    {
        return Payload::withoutNulls([
            'id' => $this->confirmation_id,
            'authorization_reference' => $this->authorization_reference,
            'total_costs' => Payload::withoutNulls([
                'excl_vat' => (float) $this->total_cost_excl_vat,
                'incl_vat' => $this->total_cost_incl_vat !== null ? (float) $this->total_cost_incl_vat : null,
            ]),
            'currency' => $this->currency,
            'eft_data' => $this->eft_data,
            'capture_status_code' => $this->capture_status_code->value,
            'capture_status_message' => $this->capture_status_message,
            'last_updated' => $this->last_updated->toIso8601ZuluString(),
        ]);
    }
}
