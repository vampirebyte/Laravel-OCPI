<?php

namespace Ocpi\Modules\Cpo\Cdrs\Actions;

use Illuminate\Support\Facades\Log;
use Ocpi\Models\Cdrs\CpoCdr;
use Ocpi\Modules\Cpo\Cdrs\Events\PushCdr;
use Throwable;

class CdrSendAction
{
    public function __construct(private readonly PushCdr $pushCdr) {}

    /**
     * POST a stored CDR to its eMSP, in the eMSP's version; sent_at marks that
     * it was accepted. A failure is logged - the CDR stays unsent, for
     * ocpi:cdrs:cpo_retry_unsent.
     */
    public function handle(CpoCdr $cdr): bool
    {
        $party = $cdr->party;
        if ($party === null) {
            return false;
        }

        try {
            $this->pushCdr->push($cdr->toOcpiObject()->toArray($party->version ?? '2.2.1'), $party->code);
        } catch (Throwable $exception) {
            Log::channel('ocpi')->error('CDR push failed', ['cdr' => $cdr->cdr_id, 'party' => $party->code, 'error' => $exception->getMessage()]);

            return false;
        }

        $cdr->update(['sent_at' => now()]);

        return true;
    }
}
