<?php

namespace Ocpi\Models\Cpo\Contracts;

/**
 * An OCPI object that serializes itself for the version asked for.
 */
interface OcpiObject
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(string $version): array;
}
