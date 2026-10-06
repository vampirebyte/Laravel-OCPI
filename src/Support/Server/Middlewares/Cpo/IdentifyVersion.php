<?php

namespace Ocpi\Support\Server\Middlewares\Cpo;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

/**
 * The CPO routes are grouped per version, so the version is passed as a
 * middleware parameter instead of being parsed from the path.
 */
class IdentifyVersion
{
    public function handle(Request $request, Closure $next, string $version): Response
    {
        Context::add('ocpi_version', $version);

        return $next($request);
    }
}
