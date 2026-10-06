<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Ocpi\Support\Server\Middlewares\Cpo\IdentifyParty;
use Ocpi\Support\Server\Middlewares\Cpo\IdentifyVersion;
use Ocpi\Support\Server\Middlewares\LogRequest;

Route::middleware(['api', LogRequest::class, IdentifyParty::class])
    ->prefix('ocpi/cpo')
    ->group(function () {
        foreach (config('ocpi-cpo.versions', []) as $version => $versionConfiguration) {
            $modules = array_keys($versionConfiguration['modules'] ?? []);

            if (count($modules) === 0) {
                continue;
            }

            Route::prefix($version)
                ->middleware(IdentifyVersion::class.':'.$version)
                ->group(function () use ($version, $modules) {
                    foreach ($modules as $module) {
                        // Credentials is version-parameterized already and loaded once, unconditionally.
                        if ($module === 'credentials') {
                            continue;
                        }

                        $path = __DIR__.'/../../Modules/Cpo/'.Str::ucfirst($module).'/Server/Endpoints/'.$version.'.php';

                        if (file_exists($path)) {
                            Route::middleware([])->group($path);
                        }
                    }
                });
        }
    });
