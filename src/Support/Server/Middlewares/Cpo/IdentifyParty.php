<?php

namespace Ocpi\Support\Server\Middlewares\Cpo;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Ocpi\Models\Party;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Traits\Server\Response as ServerResponse;
use Symfony\Component\HttpFoundation\Response;

class IdentifyParty
{
    use ServerResponse;

    public function handle(Request $request, Closure $next): Response
    {
        // Retrieve Authorization Token from header.
        $clientToken = $this->token($request, 'Token');
        if ($clientToken === null) {
            return $this->withRequestIds($request, $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::NotEnoughInformation,
                statusMessage: 'Authorization is missing.',
                httpCode: 401,
            ));
        }
        $clientToken = Str::rtrim($clientToken);

        // Decode Token (OCPI version >= 2.2).
        $clientTokenDecoded = Party::decodeToken($clientToken);

        // Retrieve Party from Token.
        $party = Party::where('client_token', $clientToken)
            ->when($clientTokenDecoded !== false, function (Builder $query) use ($clientTokenDecoded) {
                $query->orWhere('client_token', $clientTokenDecoded);
            })
            ->first();

        if ($party === null) {
            return $this->withRequestIds($request, $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: 'Invalid Authorization Token or Party.',
                httpCode: 401,
            ));
        }

        // Add information to Context.
        Context::add('cpo_trace_id', Str::uuid()->toString());
        Context::add('cpo_party_code', $party->code);
        Context::addHidden('cpo_party', $party);

        return $this->withRequestIds($request, $next($request));
    }

    /**
     * OCPI 2.2+: a response carries the X-Request-ID and X-Correlation-ID of
     * the request it answers.
     */
    private function withRequestIds(Request $request, Response $response): Response
    {
        foreach (['X-Request-ID', 'X-Correlation-ID'] as $header) {
            if ($request->hasHeader($header)) {
                $response->headers->set($header, $request->header($header));
            }
        }

        return $response;
    }

    /**
     * @see \Illuminate\Http\Concerns\InteractsWithInput::bearerToken()
     */
    private function token(Request $request, string $prefix = 'Bearer'): ?string
    {
        $header = $request->header('Authorization', '');

        $prefix .= ' ';
        $position = strrpos($header, $prefix);

        if ($position !== false) {
            $header = substr($header, $position + strlen($prefix));

            return str_contains($header, ',') ? strstr($header, ',', true) : $header;
        }

        return null;
    }
}
