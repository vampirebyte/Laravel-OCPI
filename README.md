# Laravel OCPI eMSP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/codivores/laravel-ocpi-emsp.svg?style=flat-square)](https://packagist.org/packages/codivores/laravel-ocpi-emsp)
[![Total Downloads](https://img.shields.io/packagist/dt/codivores/laravel-ocpi-emsp.svg?style=flat-square)](https://packagist.org/packages/codivores/laravel-ocpi-emsp)

Laravel package for OCPI ([Open Charge Point Interface](https://github.com/ocpi/ocpi)) protocol as eMSP (e-Mobility Service Provider).

### Key Features:

- **OCPI version:** 2.1.1
- **OCPI Modules:**
  - CDRs
  - Commands
  - Credentials & Registration
  - Locations
  - Sessions
  - Versions

### Version support

- **PHP:** `8.2`, `8.3`
- **Laravel:** `11.0`

## Installation

You can install the package via composer:

```bash
composer require codivores/laravel-ocpi-emsp
```

If you want to customize the eMSP configuration (party information, versions and available modules), you can publish the dedicated config file:

```bash
php artisan vendor:publish --tag="ocpi-emsp-config"
```

This is the content of the published config file:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Party
    |--------------------------------------------------------------------------
    */

    'party' => [
        'party_id' => env('OCPI_EMSP_PARTY_ID'),
        'country_code' => env('OCPI_EMSP_COUNTRY_CODE'),
        'business_details' => [
            'name' => env('OCPI_EMSP_NAME', env('APP_NAME')),
            'website' => env('OCPI_EMSP_WEBSITE', env('APP_URL')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Versions
    |--------------------------------------------------------------------------
    */

    'versions' => [
        '2.1.1' => [
            'modules' => [
                'cdrs',
                'commands',
                'credentials',
                'locations',
                'sessions',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Modules
    |--------------------------------------------------------------------------
    */

    'module' => [
        'cdrs' => [
            'id_separator' => env('OCPI_EMSP_MODULE_CDRS_ID_SEPARATOR', '___'),
        ],
    ],

];
```

If you want to customize the package configuration, you can publish the config file:

```bash
php artisan vendor:publish --tag="ocpi-config"
```

This is the content of the published config file:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Server
    |--------------------------------------------------------------------------
    */

    'server' => [
        'enabled' => env('OCPI_SERVER_ENABLED', true),
        'routing' => [
            'uri_prefix' => env('OCPI_SERVER_ROUTING_URI_PREFIX', 'ocpi/emsp'),
            'name_prefix' => env('OCPI_SERVER_ROUTING_NAME_PREFIX', 'ocpi.emsp.'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Client
    |--------------------------------------------------------------------------
    */

    'client' => [
        'server' => [
            'url' => env('OCPI_CLIENT_SERVER_URL', env('APP_URL')).'/'.env('OCPI_SERVER_ROUTING_URI_PREFIX', 'ocpi/emsp'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    */

    'database' => [
        'connection' => env('OCPI_DATABASE_CONNECTION', env('DB_CONNECTION', 'sqlite')),
        'table' => [
            'prefix' => env('OCPI_DATABASE_TABLE_PREFIX', 'ocpi_'),
        ],
    ],

];
```

## Getting started

#### Define the eMSP information environment variables:

```dotenv
OCPI_EMSP_PARTY_ID=MYC
OCPI_EMSP_COUNTRY_CODE=FR
OCPI_EMSP_NAME=My Company
OCPI_EMSP_WEBSITE=https://www.my-company.org
```

#### Initialize a new "Sender" Party to start credentials exchange:

```bash
php artisan ocpi:credentials:initialize
```

#### Run credentials exchange with a new "Sender" Party:

```bash
php artisan ocpi:credentials:register {party_code}
```
## Other commands

#### Update credentials and versions with a Party:

```bash
php artisan ocpi:credentials:update {party_code} {--without_new_client_token}
```

#### Synchronize locations of all or a specific Party:

```bash
php artisan ocpi:locations:synchronize {--P|party=}
```


## License

The DBAD License (DBAD). Please see [License File](LICENSE.md) for more information.


# OCPI CPO Integration

This package allows you to integrate your own charging platform with the OCPI CPO modules by providing implementations for the required contracts.

## Service Registration

Register your implementations in a service provider.

```php
use Ocpi\Models\Cpo\Contracts\LocationRepository;
use Ocpi\Models\Cpo\Contracts\CommandsContract;

$this->app->bind(LocationRepository::class, LocationOcpiService::class);
$this->app->bind(CommandsContract::class, CommandOcpiService::class);

$this->app->singleton(OcppMessagePublisherProxy::class);
$this->app->singleton(PushSession::class);
$this->app->singleton(PushCdr::class);
$this->app->singleton(PushCommandResult::class);
```

---

# Location Repository

Implement the `LocationRepository` contract.

```php
namespace Ocpi\Models\Cpo\Contracts;

use Illuminate\Support\Collection;
use Ocpi\Models\Cpo\Dto\Connector;
use Ocpi\Models\Cpo\Dto\Evse;
use Ocpi\Models\Cpo\Dto\Location;

interface LocationRepository
{
    public function getLocations(array $filters = []): Collection;

    public function countLocations(array $filters = []): int;

    public function getLocation(string $locationId): ?Location;

    public function getEvse(string $locationId, string $evseUid): ?Evse;

    public function getConnector(
        string $locationId,
        string $evseUid,
        string $connectorId
    ): ?Connector;
}
```

## Responsibilities

Your implementation is responsible for exposing your charging infrastructure as OCPI DTOs.

### getLocations()

Return a paginated collection of locations.

Supported filters:

| Filter | Description |
|---------|-------------|
| `date_from` | Updated after timestamp |
| `date_to` | Updated before timestamp |
| `offset` | Pagination offset |
| `limit` | Maximum number of results |

Example:

```php
public function getLocations(array $filters = []): Collection
{
    return Location::query()
        ->offset($filters['offset'] ?? 0)
        ->limit($filters['limit'] ?? 100)
        ->get()
        ->map(fn ($location) => $this->toDto($location));
}
```

### countLocations()

Returns the total number of locations matching the supplied filters.

### getLocation()

Returns a single Location DTO.

### getEvse()

Returns an EVSE for a given Location.

### getConnector()

Returns a Connector belonging to an EVSE.

---

## DTO Mapping

Convert your internal models into the provided DTOs.

Available DTOs:

- `Location`
- `Evse`
- `Connector`
- `Coordinates`

Example:

```php
return new LocationDto(
    id: $location->external_id,
    name: $location->name,
    address: $location->address,
    city: $location->city,
    postalCode: $location->postal_code,
    country: $location->country,
    coordinates: new Coordinates(...),
    evses: [...],
    lastUpdated: $location->updated_at->toIso8601String(),
);
```

---

# Commands

Implement the `CommandsContract`.

```php
namespace Ocpi\Models\Cpo\Contracts;

use Ocpi\Models\Cpo\Dto\CommandRequest;
use Ocpi\Models\Cpo\Dto\CommandResponse;

interface CommandsContract
{
    public function handle(CommandRequest $command): CommandResponse;
}
```

## Responsibilities

Your implementation should:

- validate incoming commands
- apply business rules
- communicate with your OCPP/backend
- return an immediate `CommandResponse`
- asynchronously notify the OCPI `response_url` when required

Example:

```php
public function handle(CommandRequest $command): CommandResponse
{
    return match ($command->type) {
        CommandType::START_SESSION      => $this->startSession($command),
        CommandType::STOP_SESSION       => $this->stopSession($command),
        CommandType::UNLOCK_CONNECTOR   => $this->unlockConnector($command),
        CommandType::RESERVE_NOW        => $this->reserveNow($command),
        CommandType::CANCEL_RESERVATION => $this->cancelReservation($command),
    };
}
```

Supported command types:

- `START_SESSION`
- `STOP_SESSION`
- `UNLOCK_CONNECTOR`
- `RESERVE_NOW`
- `CANCEL_RESERVATION`

Return either:

```php
return new CommandResponse('ACCEPTED');
```

or

```php
return new CommandResponse('REJECTED');
```

---

# Credentials Exchange

Before exchanging OCPI messages with an eMSP, initialize the remote party.

```bash
php artisan ocpi:credentials:cpo_initialize
```

The command will prompt for:

- eMSP Party name
- eMSP Party ID / Code
- eMSP Versions endpoint URL
- Client token (used by the eMSP to identify this CPO)

Example:

```text
EMSP Party name:
Hubject

EMSP Party ID or code:
HUB

EMS URL of API versions endpoint:
https://example.com/ocpi/versions

Token Used by EMSP to identify this CPO:
my-secret-token
```

This creates the remote Party record required for the OCPI Credentials module to perform the credentials handshake.

---

# Summary

To integrate your platform you must:

- Implement `LocationRepository`
- Implement `CommandsContract`
- Register both implementations in your service provider
- Register the required singleton services
- Initialize every remote eMSP using:

```bash
php artisan ocpi:credentials:cpo_initialize
```

Once configured, the package will automatically use your implementations for the OCPI Locations, Commands, and Credentials modules.