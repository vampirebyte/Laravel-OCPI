<?php

namespace Ocpi\Modules\Cpo\Payments\Actions;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Ocpi\Models\Party;
use Ocpi\Modules\Cpo\Payments\Validators\FinancialAdviceConfirmationValidator;
use Ocpi\Modules\Cpo\Payments\Validators\TerminalValidator;
use Ocpi\Support\Client\Client;

class PaymentsSynchronizeAction
{
    public function __construct(
        private readonly TerminalStoreAction $terminalStore,
        private readonly FinancialAdviceConfirmationStoreAction $confirmationStore,
    ) {}

    /**
     * Pull the PTP's terminals and financial advice confirmations (GET) and
     * store them - what it pushes to us, should a push have been missed. An
     * object that is not valid is logged and skipped.
     *
     * @param  array{date_from?: string, date_to?: string}  $query
     * @return array{terminals: int, financial_advice_confirmations: int} how many were stored
     */
    public function handle(Party $party, array $query = []): array
    {
        $payments = (new Client($party, 'payments'))->cpoPayments();

        $terminalCount = 0;
        foreach ($payments->terminals($query) as $object) {
            if (! is_array($object) || Validator::make($object, TerminalValidator::rules())->fails()) {
                Log::channel('ocpi')->warning('Payments sync - invalid terminal skipped', ['party' => $party->code, 'terminal' => $object]);

                continue;
            }

            $this->terminalStore->handle($party, $object);
            $terminalCount++;
        }

        $confirmationCount = 0;
        foreach ($payments->financialAdviceConfirmations($query) as $object) {
            if (! is_array($object) || Validator::make($object, FinancialAdviceConfirmationValidator::rules())->fails()) {
                Log::channel('ocpi')->warning('Payments sync - invalid financial advice confirmation skipped', ['party' => $party->code, 'confirmation' => $object]);

                continue;
            }

            $this->confirmationStore->handle($party, $object);
            $confirmationCount++;
        }

        return [
            'terminals' => $terminalCount,
            'financial_advice_confirmations' => $confirmationCount,
        ];
    }
}
