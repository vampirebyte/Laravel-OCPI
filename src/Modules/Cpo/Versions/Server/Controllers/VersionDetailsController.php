<?php

namespace Ocpi\Modules\Cpo\Versions\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Ocpi\Support\Enums\OcpiServerErrorCode;
use Ocpi\Support\Traits\Server\Response;

/**
 * GET /ocpi/cpo/{version}: the modules of that version we really serve.
 * A configured module is only listed when it has routes for the version
 * (credentials always have) - an advertised URL that 404s breaks eMSPs.
 * Endpoint roles exist from 2.2; a 2.1.1 endpoint is identifier + url.
 */
class VersionDetailsController
{
    use Response;

    public function __invoke(string $version): JsonResponse
    {
        // versions are keyed "2.1.1": read the list, the dots are not config nesting
        $configuration = config('ocpi-cpo.versions')[$version] ?? null;

        if (! $configuration) {
            return $this->ocpiServerErrorResponse(
                statusCode: OcpiServerErrorCode::UnsupportedVersion,
                statusMessage: 'Unsupported version.',
            );
        }

        $baseUrl = rtrim((string) $configuration['base_url'], '/');
        $withRoles = version_compare($version, '2.2', '>=');

        $endpoints = [];
        foreach ($configuration['modules'] ?? [] as $module => $settings) {
            if (! $this->hasRoutes($module, $version)) {
                continue;
            }

            $endpoint = ['identifier' => $module];
            if ($withRoles) {
                $endpoint['role'] = $settings['role'];
            }
            $endpoint['url'] = $baseUrl.'/'.$module;

            $endpoints[] = $endpoint;
        }

        return $this->ocpiSuccessResponse([
            'version' => $version,
            'endpoints' => $endpoints,
        ]);
    }

    private function hasRoutes(string $module, string $version): bool
    {
        return $module === 'credentials'
            || file_exists(__DIR__.'/../../../'.Str::ucfirst($module).'/Server/Endpoints/'.$version.'.php');
    }
}
