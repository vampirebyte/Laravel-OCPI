<?php

namespace Ocpi\Modules\Cpo\Payments\Validators;

use Illuminate\Validation\Rule;
use Ocpi\Models\Payments\Enums\CaptureStatusCode;

/**
 * A FinancialAdviceConfirmation object a PTP sends us. A failed capture
 * comes without eft_data.
 */
class FinancialAdviceConfirmationValidator
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'id' => ['required', 'string', 'max:36'],
            'authorization_reference' => ['required', 'string', 'max:36'],
            'total_costs' => ['required', 'array'],
            'total_costs.excl_vat' => ['required', 'numeric'],
            'total_costs.incl_vat' => ['nullable', 'numeric'],
            'currency' => ['required', 'string', 'size:3'],
            'eft_data' => ['nullable', 'array'],
            'eft_data.*' => ['string'],
            'capture_status_code' => ['required', Rule::enum(CaptureStatusCode::class)],
            'capture_status_message' => ['nullable', 'string', 'max:255'],
            'last_updated' => ['required', 'date'],
        ];
    }
}
