<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Ocpi\Support\Server\Middlewares\IdentifyParty;
use Ocpi\Support\Server\Middlewares\IdentifyVersion;
use Ocpi\Support\Server\Middlewares\LogRequest;

Route::middleware([
    'api',
    LogRequest::class,
    IdentifyParty::class,
    IdentifyVersion::class,
])
    ->prefix(config('ocpi.server.routing.uri_prefix'))
    ->name(config('ocpi.server.routing.name_prefix'))
    ->group(function () {
        foreach (config('ocpi-emsp.versions', []) as $version => $versionConfiguration) {
            if (count($versionConfiguration['modules'] ?? []) > 0) {
                Route::prefix($version)
                    ->name(Str::replace('.', '_', $version).'.')
                    ->group(function () use ($version, $versionConfiguration) {
                        Route::middleware([])
                            ->group(__DIR__.'/../../../Modules/Emsp/Versions/Server/Endpoints/'.$version.'.php');
                        foreach ($versionConfiguration['modules'] as $module) {
                            $emspPath = __DIR__.'/../../../Modules/Emsp/'.Str::ucfirst($module).'/Server/Endpoints/'.$version.'.php';
                            $defaultPath = __DIR__.'/../../../Modules/'.Str::ucfirst($module).'/Server/Endpoints/'.$version.'.php';
                            Route::middleware([])
                                ->group(file_exists($emspPath) ? $emspPath : $defaultPath);
                        }
                    });
            }
        }
    });
