<?php

namespace Ocpi\Support\Client;

use ArrayObject;
use Ocpi\Support\Client\Requests\DeleteRequest;
use Ocpi\Support\Client\Requests\GetRequest;
use Ocpi\Support\Client\Requests\PatchRequest;
use Ocpi\Support\Client\Requests\PostRequest;
use Ocpi\Support\Client\Requests\PutRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Resource extends BaseResource
{
    public function requestGetSend(?string $endpoint = null, ?array $query = null): ?array
    {
        $response = $this->connector->send(
            (new GetRequest)
                ->withEndpoint($endpoint)
                ->withQuery($query)
        );

        $response->throw();

        return $this->responseGetProcess($response);
    }

    public function requestPostSend(array|ArrayObject|null $payload, ?string $endpoint = null): array|string|null
    {
        $response = $this->connector->send(
            (new PostRequest)
                ->withEndpoint($endpoint)
                ->withPayload($payload)
        );

        $response->throw();

        return $this->responsePostProcess($response);
    }

    public function requestPutSend(array|ArrayObject|null $payload, ?string $endpoint = null): array|string|null
    {
        $response = $this->connector->send(
            (new PutRequest)
                ->withEndpoint($endpoint)
                ->withPayload($payload)
        );

        $response->throw();

        return $this->responsePutProcess($response);
    }

    public function requestPatchSend(array|ArrayObject|null $payload, ?string $endpoint = null): array|string|null
    {
        $response = $this->connector->send(
            (new PatchRequest)
                ->withEndpoint($endpoint)
                ->withPayload($payload)
        );

        $response->throw();

        return $this->responsePatchProcess($response);
    }

    public function requestDeleteSend(?string $endpoint = null): array|string|null
    {
        $response = $this->connector->send(
            (new DeleteRequest)
                ->withEndpoint($endpoint)
        );

        $response->throw();

        return $this->responseDeleteProcess($response);
    }

    public function responseGetProcess(Response $response): ?array
    {
        if (! $response->successful()) {
            return null;
        }

        $responseArray = $response->array();

        return $responseArray['data'] ?? null;

        return ($responseObject?->data ?? null) ? (array) $responseObject->data : null;
    }

    public function responsePostProcess(Response $response): array|string|null
    {
        if (! $response->successful()) {
            return null;
        }

        $responseArray = $response->array();

        return $responseArray['data'] ?? null;
    }

    public function responsePutProcess(Response $response): array|string|null
    {
        if (! $response->successful()) {
            return null;
        }

        $responseArray = $response->array();

        return $responseArray['data'] ?? null;
    }

    public function responsePatchProcess(Response $response): array|string|null
    {
        if (! $response->successful()) {
            return null;
        }

        $responseArray = $response->array();

        return $responseArray['data'] ?? null;
    }

    public function responseDeleteProcess(Response $response): array|string|null
    {
        if (! $response->successful()) {
            return null;
        }

        $responseArray = $response->array();

        return $responseArray['data'] ?? null;
    }
}
