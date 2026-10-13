<?php

namespace Ocpi\Support\Server\Middlewares\Cpo;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Ocpi\Models\Party;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Traits\Server\Response as ServerResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only a party with the given role may call the routes - e.g. the payments
 * module is for PTPs: EnsurePartyRole::class.':PTP'. Runs after IdentifyParty.
 */
class EnsurePartyRole
{
    use ServerResponse;

    public function handle(Request $request, Closure $next, string $role): Response
    {
        $party = Context::getHidden('cpo_party');

        if (! $party instanceof Party || ! $party->hasRole($role)) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: 'Only a '.$role.' party may call this module.',
                httpCode: 403,
            );
        }

        return $next($request);
    }
}
