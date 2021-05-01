<?php

namespace App\Rules;

use Ramsey\Uuid\Rfc4122\Validator;
use Illuminate\Contracts\Validation\Rule;

class ArrayUuid implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $validator = new Validator();
        if (!is_array($value)) {
            return false;
        }
        foreach($value as $v) {
            if(!$validator->validate($v)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be valid array of uuid.';
    }
}
