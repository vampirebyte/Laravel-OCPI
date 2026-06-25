<?php

namespace Ocpi\Modules\CPO\Versions\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Ocpi\Support\Traits\Server\Response;

class VersionDetailsController
{
    use Response;

    public function __invoke(string $version): JsonResponse
    {
        $config = config("ocpi-cpo.versions");
        $versions = $config[$version] ?? [];
        $modules = $config[$version]['modules'] ?? [];
        $baseUrl = $config[$version]['base_url'] ?? [];

        if (!$versions) {
            return $this->ocpiServerErrorResponse();
        }

        $endpoints = [];
        foreach ($modules as $module => $settings) {
            $endpoints[] = [
                'identifier' => $module,
                'role' => $settings['role'],
                'url' => rtrim($baseUrl, '/') . '/' . $module,
            ];
        }

        return $this->ocpiSuccessResponse([
            'version' => $version,
            'endpoints' => $endpoints,
        ]);
    }
}
