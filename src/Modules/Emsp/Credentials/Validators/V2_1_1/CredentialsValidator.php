<?php

namespace Ocpi\Modules\Emsp\Credentials\Validators\V2_1_1;

use Illuminate\Support\Facades\Validator;

class CredentialsValidator
{
    protected static array $rules = [
        'token' => 'required',
        'url' => 'required',
        'business_details' => 'required|array:name,website,logo',
        'business_details.name' => 'required',
        'party_id' => 'required',
        'country_code' => 'required',
    ];

    public static function validate(array $input = []): array
    {
        return Validator::make($input, self::$rules)
            ->validate();
    }
}
