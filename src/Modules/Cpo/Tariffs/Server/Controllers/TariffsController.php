<?php

namespace Ocpi\Modules\Cpo\Tariffs\Server\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Ocpi\Models\Cpo\Contracts\TariffRepository;
use Ocpi\Support\Server\Controllers\Controller;
use Ocpi\Support\Traits\Server\Paginates;

/**
 * The Tariffs sender interface: GET tariffs, paginated, filtered by
 * date_from / date_to - only the calling eMSP's own, serialized for the
 * version the request came in on.
 */
class TariffsController extends Controller
{
    use Paginates;

    public function __construct(
        private readonly TariffRepository $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $party = Context::getHidden('cpo_party');
        $version = (string) Context::get('ocpi_version', '2.1.1');

        [$offset, $limit] = $this->paginationWindow($request);
        $dateFilters = $request->only(['date_from', 'date_to']);

        $items = [];
        foreach ($this->repository->getTariffs($party, [...$dateFilters, 'offset' => $offset, 'limit' => $limit]) as $item) {
            $items[] = $item->toArray($version);
        }

        return $this->paginatedResponse($request, $items, $this->repository->countTariffs($party, $dateFilters), $offset, $limit);
    }
}
