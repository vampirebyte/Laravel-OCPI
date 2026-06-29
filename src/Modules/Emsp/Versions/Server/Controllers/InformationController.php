<?php

namespace Ocpi\Modules\Emsp\Versions\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Ocpi\Support\Server\Controllers\Controller;

class InformationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = collect(config('ocpi-emsp.versions', []))
            ->map(function ($moduleList, $version) {
                $route = config('ocpi.server.routing.name_prefix').Str::replace('.', '_', $version).'.versions.details';

                return Route::has($route)
                    ? [
                        'version' => $version,
                        'url' => route($route),
                    ]
                    : null;
            })
            ->filter()
            ->values()
            ->toArray();

        return $this->ocpiSuccessResponse($data);
    }
}
