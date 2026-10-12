<?php

namespace Ocpi\Modules\Cpo\Tokens\Client;

use Ocpi\Support\Client\Requests\PostRequest;
use Ocpi\Support\Client\Resource as OcpiResource;

class Resource extends OcpiResource
{
    /**
     * POST {tokens}/{token_uid}/authorize?type= - real-time authorization at the eMSP.
     *
     * @param  array<string, mixed>|null  $locationReferences
     * @return array<string, mixed>|null the AuthorizationInfo
     */
    public function authorize(string $tokenUid, string $type, ?array $locationReferences): ?array
    {
        $response = $this->connector->send(
            (new PostRequest)
                ->withEndpoint(rawurlencode($tokenUid).'/authorize')
                ->withQuery(['type' => $type])
                ->withPayload($locationReferences)
        );

        $response->throw();

        $data = $this->responsePostProcess($response);

        return is_array($data) ? $data : null;
    }
}
