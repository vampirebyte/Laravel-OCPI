<?php

namespace Ocpi\Support\Traits\Server;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OCPI pagination of GET list endpoints (2.1.1 and 2.2.1): offset / limit
 * query parameters, answered with X-Total-Count, X-Limit and - when there
 * is more - a Link header to the next page.
 */
trait Paginates
{
    /**
     * The asked offset and limit, the limit capped at ocpi-cpo.pagination.max_limit.
     *
     * @return array{0: int, 1: int}
     */
    protected function paginationWindow(Request $request): array
    {
        $maxLimit = (int) config('ocpi-cpo.pagination.max_limit', 100);
        $offset = max(0, (int) $request->query('offset', 0));
        $limit = (int) $request->query('limit', $maxLimit);

        return [$offset, $limit > 0 ? min($limit, $maxLimit) : $maxLimit];
    }

    /**
     * @param  array<int, mixed>  $items  the page, already serialized
     */
    protected function paginatedResponse(Request $request, array $items, int $total, int $offset, int $limit): JsonResponse
    {
        $response = $this->ocpiSuccessResponse($items)
            ->header('X-Total-Count', (string) $total)
            ->header('X-Limit', (string) $limit);

        if ($offset + $limit < $total) {
            $next = $request->fullUrlWithQuery(['offset' => $offset + $limit, 'limit' => $limit]);
            $response->header('Link', '<'.$next.'>; rel="next"');
        }

        return $response;
    }
}
