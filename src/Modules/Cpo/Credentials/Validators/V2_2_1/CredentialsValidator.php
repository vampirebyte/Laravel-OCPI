<?php

namespace Ocpi\Modules\Cpo\Credentials\Validators\V2_2_1;

use Illuminate\Support\Facades\Validator;

class CredentialsValidator
{
    /**
     * PTP (Payment Terminal Provider) is not a 2.2.1 role: it comes with the
     * DirectPayment `payments` module, an extension of 2.2.1 (part of OCPI 2.3.0).
     */
    protected static array $rules = [
        'token' => 'required|string',
        'url' => 'required|string',
        'roles' => 'required|array|min:1',
        'roles.*.role' => 'required|in:CPO,EMSP,HUB,NAP,NSP,OTHER,PTP,SCSP',
        'roles.*.party_id' => 'required|string',
        'roles.*.country_code' => 'required|string',
        'roles.*.business_details' => 'required|array:name,website,logo',
        'roles.*.business_details.name' => 'required',
    ];

    public static function validate(array $input = []): array
    {
        return Validator::make($input, self::$rules)
            ->validate();
    }
}
