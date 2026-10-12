<?php

namespace Ocpi\Support;

/**
 * Helpers for building OCPI payloads.
 */
class Payload
{
    /**
     * Drop the fields that are null - OCPI optional fields are left out rather
     * than sent empty. false, 0 and "" are kept.
     *
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    public static function withoutNulls(array $fields): array
    {
        foreach ($fields as $key => $value) {
            if ($value === null) {
                unset($fields[$key]);
            }
        }

        return $fields;
    }
}
