<?php

namespace Ocpi\Modules\Cpo\Cdrs\Console\Commands;

use Illuminate\Console\Command;
use Ocpi\Models\Cdrs\CpoCdr;
use Ocpi\Modules\Cpo\Cdrs\Actions\CdrSendAction;

class RetryUnsent extends Command
{
    protected $signature = 'ocpi:cdrs:cpo_retry_unsent';

    protected $description = 'Send again the CDRs their EMSP has not accepted yet - meant to be scheduled';

    /**
     * A CDR is left alone for its first minutes - whoever stored it is still
     * sending it - and given up after some days; the eMSP can still pull it.
     */
    public function handle(CdrSendAction $cdrSendAction): int
    {
        $unsentCdrs = CpoCdr::query()
            ->whereNull('sent_at')
            ->where('created_at', '<', now()->subMinutes((int) config('ocpi-cpo.cdrs.retry_after_minutes', 5)))
            ->where('created_at', '>', now()->subDays((int) config('ocpi-cpo.cdrs.retry_for_days', 3)))
            ->lazyById();

        $sent = 0;
        $failed = 0;

        foreach ($unsentCdrs as $cdr) {
            if ($cdrSendAction->handle($cdr)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        $this->info("{$sent} CDR(s) sent, {$failed} still unsent.");

        return self::SUCCESS;
    }
}
