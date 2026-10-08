<?php

namespace Ocpi\Modules\Cpo\Credentials\Validators;

use Ocpi\Modules\Cpo\Credentials\Validators\V2_1_1\CredentialsValidator as CredentialsValidatorV211;
use Ocpi\Modules\Cpo\Credentials\Validators\V2_2_1\CredentialsValidator as CredentialsValidatorV221;

/**
 * Validates a Credentials object for the given OCPI version and normalizes it
 * to the 2.2+ shape, so 2.1.1 input comes back with a single EMSP role.
 */
class CredentialsValidator
{
    /**
     * @return array{token: string, url: string, roles: array<int, array{role: string, party_id: string, country_code: string, business_details: array<string, mixed>}>}
     */
    public static function validate(array $input, ?string $version): array
    {
        if (self::hasRoles($version)) {
            $validated = CredentialsValidatorV221::validate($input);

            return [
                'token' => $validated['token'],
                'url' => $validated['url'],
                'roles' => array_map(fn (array $role) => [
                    'role' => $role['role'],
                    'party_id' => $role['party_id'],
                    'country_code' => $role['country_code'],
                    'business_details' => $role['business_details'],
                ], array_values($validated['roles'])),
            ];
        }

        $validated = CredentialsValidatorV211::validate($input);

        return [
            'token' => $validated['token'],
            'url' => $validated['url'],
            'roles' => [[
                'role' => 'EMSP',
                'party_id' => $validated['party_id'],
                'country_code' => $validated['country_code'],
                'business_details' => $validated['business_details'],
            ]],
        ];
    }

    /**
     * OCPI 2.2+ replaced the flat party_id / country_code / business_details with a roles list.
     */
    public static function hasRoles(?string $version): bool
    {
        return $version !== null && version_compare($version, '2.2', '>=');
    }
}
