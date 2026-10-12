<?php

namespace Ocpi\Models\Tokens\Dto;

/**
 * The eMSP's answer to a real-time authorization request - the same meaning
 * in 2.1.1 and 2.2.1 (only 2.2.1 has an authorization_reference).
 */
class AuthorizationInfo
{
    /**
     * @param  string  $allowed  ALLOWED, BLOCKED, EXPIRED, NO_CREDIT or NOT_ALLOWED
     */
    public function __construct(
        public readonly string $allowed,
        public readonly ?string $authorizationReference = null,
    ) {}

    public function isAllowed(): bool
    {
        return $this->allowed === 'ALLOWED';
    }
}
