<?php

namespace Ocpi\Models\Cpo\Dto;

use Ocpi\Models\Cpo\Contracts\OcpiObject;

/**
 * An OCPI object stored as it was sent, in the shape of each version - e.g.
 * a CDR, which never changes once sent.
 */
class StoredObject implements OcpiObject
{
    /**
     * @param  array<string, mixed>  $object211  the 2.1.1 shape
     * @param  array<string, mixed>  $object221  the 2.2.1 shape
     */
    public function __construct(
        public readonly array $object211,
        public readonly array $object221,
    ) {}

    public function toArray(string $version): array
    {
        return version_compare($version, '2.2', '<') ? $this->object211 : $this->object221;
    }
}
