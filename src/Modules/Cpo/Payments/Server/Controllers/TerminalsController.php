<?php

namespace Ocpi\Modules\Cpo\Payments\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Validator;
use Ocpi\Models\Party;
use Ocpi\Models\Payments\PaymentTerminal;
use Ocpi\Modules\Cpo\Payments\Actions\TerminalStoreAction;
use Ocpi\Modules\Cpo\Payments\Validators\TerminalValidator;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Server\Controllers\Controller;
use Ocpi\Support\Traits\Server\Paginates;

/**
 * The payments module receiver interface for terminals (DirectPayment
 * extension): a PTP creates its terminals here (POST) and reads back what we
 * hold (GET, paginated, filtered on last_updated) - only its own.
 */
class TerminalsController extends Controller
{
    use Paginates;

    public function __construct(
        private readonly TerminalStoreAction $terminalStore,
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

        $query = PaymentTerminal::query()
            ->where('party_id', $this->caller()->id)
            ->createdByPtp()
            ->lastUpdatedBetween($request->only(['date_from', 'date_to']));

        $total = (clone $query)->count();

        $items = [];
        foreach ($query->orderBy('id')->offset($offset)->limit($limit)->get() as $terminal) {
            $items[] = $terminal->toOcpiArray();
        }

        return $this->paginatedResponse($request, $items, $total, $offset, $limit);
    }

    public function show(string $terminalId): JsonResponse
    {
        $terminal = PaymentTerminal::query()
            ->where('party_id', $this->caller()->id)
            ->where('terminal_id', $terminalId)
            ->first();

        if (! $terminal) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: 'Unknown terminal.',
                httpCode: 404,
            );
        }

        return $this->ocpiSuccessResponse($terminal->toOcpiArray());
    }

    /**
     * A new terminal - or one we asked for with an activation request - or
     * a terminal sent again with changes.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), TerminalValidator::rules());

        if ($validator->fails()) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: $validator->errors()->first(),
            );
        }

        $terminal = $this->terminalStore->handle($this->caller(), $request->all());

        return $this->ocpiSuccessResponse($terminal->toOcpiArray());
    }

    private function caller(): Party
    {
        return Context::getHidden('cpo_party');
    }
}
