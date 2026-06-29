<?php

declare(strict_types=1);

namespace Ocpi;

use Illuminate\Support\ServiceProvider;
use Ocpi\Models\Cpo\Contracts\LocationRepository;
use Ocpi\Models\Cpo\Repositories\NullLocationRepository;
use Ocpi\Modules\Emsp\Credentials\Console\Commands\Initialize as ModuleCredentialsInitialize;
use Ocpi\Modules\Emsp\Credentials\Console\Commands\Register as ModuleCredentialsRegister;
use Ocpi\Modules\Emsp\Credentials\Console\Commands\Update as ModuleCredentialsUpdate;
use Ocpi\Modules\Emsp\Locations\Console\Commands\Synchronize as ModuleLocationsSynchronize;
use Ocpi\Modules\Emsp\Versions\Console\Commands\Update as ModuleVersionsUpdate;
use Ocpi\Modules\Cpo\Credentials\Console\Commands\Initialize as CpoModuleCredentialsInitialize;

class OcpiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bindIf(LocationRepository::class, NullLocationRepository::class);

        $this->mergeConfigFrom(
            __DIR__ . '/../config/ocpi.php',
            'ocpi'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../config/ocpi-emsp.php',
            'ocpi-emsp'
        );

        # CPO
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ocpi-cpo.php',
            'ocpi-cpo'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrations();
            $this->publishConfig();
            $this->registerCommands();
        }

        $this->loadRoutes();
        $this->setLoggingChannel();
    }

    private function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Data/Migrations');
    }

    private function loadRoutes(): void
    {
        if (config('ocpi.server.enabled', false) === true) {

            $cpoVersionList = config('ocpi-cpo.versions', []);
            if (count($cpoVersionList) > 0) {
                $this->loadRoutesFrom(__DIR__ . '/Modules/Cpo/Versions/Server/Endpoints/2.1.1.php');
                $this->loadRoutesFrom(__DIR__ . '/Modules/Cpo/Credentials/Server/Endpoints/2.1.1.php');
                $this->loadRoutesFrom(__DIR__ . '/Modules/Cpo/Locations/Server/Endpoints/2.1.1.php');
            }

            $emspVersionList = config('ocpi-emsp.versions', []);
            if (count($emspVersionList) > 0) {
                $this->loadRoutesFrom(__DIR__ . '/Support/Server/Endpoints/common.php');
                $this->loadRoutesFrom(__DIR__ . '/Support/Server/Endpoints/version.php');
            }
        }
    }

    private function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ocpi.php' => config_path('ocpi.php'),
        ], 'ocpi-config');

        $this->publishes([
            __DIR__ . '/../config/ocpi-emsp.php' => config_path('ocpi-emsp.php'),
        ], 'ocpi-emsp-config');
    }

    private function registerCommands(): void
    {
        $this->commands([
            ModuleVersionsUpdate::class,
            ModuleCredentialsInitialize::class,
            ModuleCredentialsRegister::class,
            ModuleCredentialsUpdate::class,
            ModuleLocationsSynchronize::class,
            CpoModuleCredentialsInitialize::class,
        ]);
    }

    private function setLoggingChannel(): void
    {
        app('config')
            ->set(
                'logging.channels.ocpi',
                [
                    'driver' => 'daily',
                    'path' => storage_path('logs/ocpi.log'),
                    'level' => env('OCPI_LOG_LEVEL', 'debug'),
                    'days' => env('OCPI_LOG_DAILY_DAYS', 60),
                ]
            );
    }
}
