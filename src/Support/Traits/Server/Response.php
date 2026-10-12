<?php

declare(strict_types=1);

namespace Ocpi\Support\Traits\Server;

use Illuminate\Http\JsonResponse;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Enums\OcpiServerErrorCode;

trait Response
{
    protected function ocpiSuccessResponse(mixed $data = null, $statusMessage = 'Success'): JsonResponse
    {
        return $this->ocpiResponse(data: $data, httpCode: 200, statusCode: 1000, statusMessage: $statusMessage);
    }

    protected function ocpiCreatedResponse(mixed $data = null, $statusMessage = null): JsonResponse
    {
        return $this->ocpiResponse(data: $data, httpCode: 201, statusCode: 1000, statusMessage: $statusMessage);
    }

    protected function ocpiClientErrorResponse(OcpiClientErrorCode $statusCode = OcpiClientErrorCode::Generic, $statusMessage = 'Error', int $httpCode = 400): JsonResponse
    {
        return $this->ocpiResponse(httpCode: $httpCode, statusCode: $statusCode, statusMessage: $statusMessage);
    }

    protected function ocpiServerErrorResponse(OcpiServerErrorCode $statusCode = OcpiServerErrorCode::Generic, $statusMessage = 'Error', int $httpCode = 400): JsonResponse
    {
        return $this->ocpiResponse(httpCode: $httpCode, statusCode: $statusCode, statusMessage: $statusMessage);
    }

    protected function ocpiResponse(mixed $data = null, $httpCode = null, $statusCode = null, $statusMessage = null): JsonResponse
    {
        $payload = collect([])
            ->when($data !== null, function ($payload) use ($data) {
                return $payload->put('data', $data);
            })
            ->put('status_code', $statusCode)
            ->when($statusMessage !== null, function ($payload) use ($statusMessage) {
                return $payload->put('status_message', $statusMessage);
            })
            ->put('timestamp', now()->toIso8601ZuluString());

        return response()
            ->json($payload, $httpCode)
            ->header('Content-Type', 'application/json');
    }
}
