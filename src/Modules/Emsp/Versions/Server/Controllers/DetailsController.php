<?php

namespace Ocpi\Modules\Emsp\Versions\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

class DetailsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! Context::has('ocpi_version')) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: 'Unknown OCPI version.',
            );
        }

        $version = Context::get('ocpi_version');
        $data = null;

        foreach (config('ocpi-emsp.versions', []) as $configVersion => $configInformation) {
            if ($configVersion === $version) {
                $routeVersion = Str::replace('.', '_', $version);

                $endpointList = collect(($configInformation['modules'] ?? []))
                    ->map(function ($module) use ($routeVersion) {
                        $route = config('ocpi.server.routing.name_prefix').$routeVersion.'.'.$module;

                        return Route::has($route)
                            ? [
                                'identifier' => $module,
                                'url' => route($route),
                            ]
                            : null;
                    })
                    ->filter()
                    ->values()
                    ->toArray();

                if (count($endpointList) > 0) {
                    $data = [
                        'version' => $version,
                        'endpoints' => $endpointList,
                    ];
                }
            }
        }

        if ($data === null) {
            return $this->ocpiClientErrorResponse(
                statusCode: OcpiClientErrorCode::InvalidParameters,
                statusMessage: 'Unsupported OCPI version.',
            );
        }

        return $this->ocpiSuccessResponse($data);
    }
}
