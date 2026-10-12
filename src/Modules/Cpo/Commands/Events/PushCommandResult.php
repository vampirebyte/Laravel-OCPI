<?php

namespace Ocpi\Modules\Cpo\Commands\Events;

use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Ocpi\Models\Commands\Enums\CommandResultType;
use Ocpi\Models\Party;
use Ocpi\Support\Payload;
use RuntimeException;

/**
 * POSTs the asynchronous result of a command to the response_url the eMSP
 * gave with it, in the shape of the eMSP's version:
 * - 2.1.1: a CommandResponse {result}, result one of ACCEPTED, REJECTED,
 *   TIMEOUT, NOT_SUPPORTED, UNKNOWN_SESSION;
 * - 2.2.1: a CommandResult {result, message?}, result a CommandResultType.
 */
class PushCommandResult
{
    /**
     * 2.2.1 CommandResultType => the closest 2.1.1 CommandResponseType.
     */
    private const RESULTS_2_1_1 = [
        'ACCEPTED' => 'ACCEPTED',
        'CANCELED_RESERVATION' => 'ACCEPTED',
        'EVSE_OCCUPIED' => 'REJECTED',
        'EVSE_INOPERATIVE' => 'REJECTED',
        'FAILED' => 'REJECTED',
        'NOT_SUPPORTED' => 'NOT_SUPPORTED',
        'REJECTED' => 'REJECTED',
        'TIMEOUT' => 'TIMEOUT',
        'UNKNOWN_RESERVATION' => 'REJECTED',
    ];

    /**
     * @param  string  $result  a CommandResultType name, e.g. ACCEPTED or EVSE_OCCUPIED
     */
    public function push(string $responseUrl, string $partyCode, string $result, ?string $message = null): void
    {
        $party = Party::where('code', $partyCode)->firstOrFail();

        if (CommandResultType::fromName($result) === null) {
            $result = 'FAILED';
        }

        $body = version_compare((string) $party->version, '2.2', '<')
            ? ['result' => self::RESULTS_2_1_1[$result]]
            : Payload::withoutNulls([
                'result' => $result,
                'message' => $message !== null ? [['language' => 'en', 'text' => $message]] : null,
            ]);

        $response = Http::withHeaders([
            'Authorization' => 'Token '.$party->encoded_server_token,
            'Content-Type' => 'application/json',
            'X-Request-ID' => Str::uuid()->toString(),
            'X-Correlation-ID' => (string) Context::get('trace_id', Str::uuid()->toString()),
        ])->post($responseUrl, $body);

        Log::channel('ocpi')->info('Party '.$party->code.' - command result sent', [
            'url' => $responseUrl,
            'body' => $body,
            'http_status' => $response->status(),
            'response' => $response->json(),
        ]);

        if ($response->failed() || ($response->json('status_code') ?? 1000) >= 2000) {
            throw new RuntimeException("Command result to {$partyCode} not accepted: HTTP {$response->status()}");
        }
    }
}
