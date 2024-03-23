<?php

namespace App\Rules;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Facades\Passkey\PasskeyRegistrar;
use App\Http\Controllers\PasskeyController;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPasskeyRegistrationRule implements ValidationRule
{
    public function __construct(private Request $request){}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!PasskeyRegistrar::setChallenge(Session::get(config('passkey.session')))->validateSilent($this->request->all()))
            $fail('Invalid registration.');
    }
}
