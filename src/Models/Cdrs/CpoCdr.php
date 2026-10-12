<?php

namespace Ocpi\Models\Cdrs;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Ocpi\Models\Cpo\Dto\StoredObject;
use Ocpi\Models\Party;
use Ocpi\Support\Models\Model;

/**
 * A CDR we, the CPO, send to an eMSP (Cdrs sender interface), stored as sent.
 * Not to be confused with Cdr: the CDRs other CPOs send us, the eMSP.
 *
 * @property int $id
 * @property int $party_id the eMSP it is for
 * @property string $cdr_id the OCPI CDR id
 * @property string|null $reference what the host application made it from
 * @property string $currency
 * @property string $total_cost_excl_vat
 * @property string|null $total_cost_incl_vat
 * @property array<string, mixed> $object_2_1_1
 * @property array<string, mixed> $object_2_2_1
 * @property Carbon|null $sent_at when the eMSP accepted it
 * @property-read Party $party
 */
class CpoCdr extends Model
{
    protected $fillable = [
        'party_id',
        'cdr_id',
        'reference',
        'currency',
        'total_cost_excl_vat',
        'total_cost_incl_vat',
        'object_2_1_1',
        'object_2_2_1',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'total_cost_excl_vat' => 'decimal:4',
            'total_cost_incl_vat' => 'decimal:4',
            'object_2_1_1' => 'array',
            'object_2_2_1' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Party, $this>
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * The CDR as sent, in the shape of any version.
     */
    public function toOcpiObject(): StoredObject
    {
        return new StoredObject($this->object_2_1_1, $this->object_2_2_1);
    }

    /**
     * What the driver is charged: incl. VAT when known.
     */
    public function totalCost(): float
    {
        return (float) ($this->total_cost_incl_vat ?? $this->total_cost_excl_vat);
    }
}
