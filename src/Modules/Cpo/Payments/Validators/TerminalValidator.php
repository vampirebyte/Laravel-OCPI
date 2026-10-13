<?php

namespace Ocpi\Modules\Cpo\Payments\Validators;

use Illuminate\Validation\Rule;
use Ocpi\Models\Payments\Enums\InvoiceCreator;

/**
 * A Terminal object a PTP sends us. The ids are held to their OCPI length -
 * they are stored as such; the address fields are only shown, so a longer
 * one does not turn the whole terminal down.
 */
class TerminalValidator
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'terminal_id' => ['required', 'string', 'max:36'],
            'customer_reference' => ['nullable', 'string', 'max:36'],
            'party_id' => ['nullable', 'string', 'max:3'],
            'country_code' => ['nullable', 'string', 'max:2'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'max:3'],
            'coordinates' => ['nullable', 'array'],
            'coordinates.latitude' => ['required_with:coordinates'],
            'coordinates.longitude' => ['required_with:coordinates'],
            'invoice_base_url' => ['nullable', 'string', 'max:255'],
            'invoice_creator' => ['nullable', Rule::enum(InvoiceCreator::class)],
            'serial_number' => ['nullable', 'string', 'max:36'],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['string', 'max:36'],
            'evse_uids' => ['nullable', 'array'],
            'evse_uids.*' => ['string', 'max:36'],
            'last_updated' => ['required', 'date'],
        ];
    }
}
