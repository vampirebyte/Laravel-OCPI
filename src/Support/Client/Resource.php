<?php

namespace Ocpi\Support\Client;

use ArrayObject;
use Illuminate\Support\Str;
use Ocpi\Support\Client\Requests\DeleteRequest;
use Ocpi\Support\Client\Requests\GetRequest;
use Ocpi\Support\Client\Requests\PatchRequest;
use Ocpi\Support\Client\Requests\PostRequest;
use Ocpi\Support\Client\Requests\PutRequest;
use RuntimeException;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Resource extends BaseResource
{
    /**
     * Most pages requestGetAllPagesSend follows - a guard against a server
     * that keeps linking to a next page.
     */
    private const MAX_PAGES = 1000;

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

    /**
     * A paginated GET list, every page: OCPI gives the URL of the next page in
     * the Link header, until the last one.
     *
     * @return array<int, mixed>
     */
    public function requestGetAllPagesSend(?string $endpoint = null, ?array $query = null): array
    {
        $items = [];
        $request = (new GetRequest)
            ->withEndpoint($endpoint)
            ->withQuery($query);

        for ($page = 1; $page <= self::MAX_PAGES; $page++) {
            $response = $this->connector->send($request);
            $response->throw();

            foreach ($response->array()['data'] ?? [] as $item) {
                $items[] = $item;
            }

            $nextPageUrl = $this->nextPageUrl($response);
            if ($nextPageUrl === null) {
                break;
            }

            // The next page URL is absolute, its query included. Saloon only lets
            // an absolute URL replace the module's URL when allowed: it is followed
            // on the module's own host only, so a party cannot send our token elsewhere.
            if (! $this->isOnBaseUrlHost($nextPageUrl)) {
                throw new RuntimeException("The next page is not on the host of {$this->connector->resolveBaseUrl()}: {$nextPageUrl}");
            }

            $request = (new GetRequest)->withEndpoint($nextPageUrl);
            $request->allowBaseUrlOverride = true;
        }

        return $items;
    }

    /**
     * Whether a URL has the scheme, host and port of the module's URL.
     */
    private function isOnBaseUrlHost(string $url): bool
    {
        $baseUrl = parse_url($this->connector->resolveBaseUrl());
        $nextUrl = parse_url($url);

        if (! is_array($baseUrl) || ! is_array($nextUrl) || ! isset($baseUrl['host'], $nextUrl['host'])) {
            return false;
        }

        return strtolower($nextUrl['scheme'] ?? '') === strtolower($baseUrl['scheme'] ?? '')
            && strtolower($nextUrl['host']) === strtolower($baseUrl['host'])
            && $this->portOf($nextUrl) === $this->portOf($baseUrl);
    }

    /**
     * The port of a parsed URL, the scheme's default when none is given.
     *
     * @param  array<string, int|string>  $url
     */
    private function portOf(array $url): ?int
    {
        if (isset($url['port'])) {
            return (int) $url['port'];
        }

        return match (strtolower($url['scheme'] ?? '')) {
            'https' => 443,
            'http' => 80,
            default => null,
        };
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

    /**
     * The URL in a Link header like `<https://...?offset=50&limit=50>; rel="next"`.
     * The header may list several links, separated by commas.
     */
    private function nextPageUrl(Response $response): ?string
    {
        $link = $response->header('Link');
        if (is_array($link)) {
            $link = implode(',', $link);
        }

        if (! is_string($link)) {
            return null;
        }

        foreach (explode(',', $link) as $linkPart) {
            $isNextPage = str_contains(strtolower($linkPart), 'rel="next"') || str_contains(strtolower($linkPart), 'rel=next');

            if ($isNextPage && str_contains($linkPart, '<') && str_contains($linkPart, '>')) {
                return Str::between($linkPart, '<', '>');
            }
        }

        return null;
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
