<?php

namespace Ocpi\Modules\Cpo\Payments\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Validator;
use Ocpi\Models\Party;
use Ocpi\Models\Payments\FinancialAdviceConfirmation;
use Ocpi\Modules\Cpo\Payments\Actions\FinancialAdviceConfirmationStoreAction;
use Ocpi\Modules\Cpo\Payments\Validators\FinancialAdviceConfirmationValidator;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Server\Controllers\Controller;
use Ocpi\Support\Traits\Server\Paginates;

/**
 * The payments module receiver interface for financial advice confirmations
 * (DirectPayment extension): after capturing a session's cost on the card, a
 * PTP tells us here (POST) and reads back what we hold (GET, paginated,
 * filtered on last_updated) - only its own.
 */
class FinancialAdviceConfirmationsController extends Controller
{
    use Paginates;

    public function __construct(
        private readonly FinancialAdviceConfirmationStoreAction $confirmationStore,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: $validator->errors()->first(),
            );
        }

        [$offset, $limit] = $this->paginationWindow($request);

        $query = FinancialAdviceConfirmation::query()
            ->where('party_id', $this->caller()->id)
            ->lastUpdatedBetween($request->only(['date_from', 'date_to']));

        $total = (clone $query)->count();

        $items = [];
        foreach ($query->orderBy('id')->offset($offset)->limit($limit)->get() as $confirmation) {
            $items[] = $confirmation->toOcpiArray();
        }

        return $this->paginatedResponse($request, $items, $total, $offset, $limit);
    }

    public function show(string $confirmationId): JsonResponse
    {
        $confirmation = FinancialAdviceConfirmation::query()
            ->where('party_id', $this->caller()->id)
            ->where('confirmation_id', $confirmationId)
            ->first();

        if (! $confirmation) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: 'Unknown financial advice confirmation.',
                httpCode: 404,
            );
        }

        return $this->ocpiSuccessResponse($confirmation->toOcpiArray());
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), FinancialAdviceConfirmationValidator::rules());

        if ($validator->fails()) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: $validator->errors()->first(),
            );
        }

        $this->confirmationStore->handle($this->caller(), $request->all());

        return $this->ocpiSuccessResponse();
    }

    private function caller(): Party
    {
        return Context::getHidden('cpo_party');
    }
}
