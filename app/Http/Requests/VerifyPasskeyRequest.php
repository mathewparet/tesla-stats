<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Facades\Passkey\PasskeyAuthenticator;

class VerifyPasskeyRequest extends FormRequest
{
    public $publicKeyCredentialSource;
    
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

    public function after(): array
    {
        return [
            function(Validator $validator)
            {
                Log::debug('Validating passkey...', ['passkey' => $this->passkey]);
                
                $this->publicKeyCredentialSource = PasskeyAuthenticator::validate($this->passkey);
                
                Log::debug('Validation completed', ['publicKeyCredentialSource' => $this->publicKeyCredentialSource]);

                if(!$this->publicKeyCredentialSource)
                    $validator->errors()->add('passkey', __('Invalid passkey.'));
            }
        ];
    }
}
