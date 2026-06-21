<?php

namespace Ocpi\Modules\CPO\Versions\Server\Controllers;

use Ocpi\Support\Traits\Server\Response;

class VersionsController
{
    use Response;

    public function __invoke()
    {
        $data = [];
        foreach (config('ocpi-cpo.versions') as $version => $config) {
            $data[] = [
                'version' => (string) $version,
                'url' => rtrim($config['base_url'], '/'),
            ];
        }

        return $this->ocpiSuccessResponse($data);
    }
}
