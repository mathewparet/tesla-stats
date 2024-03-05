<?php

namespace App\Http\Requests;

use App\Facades\TeslaAPIServiceManager;
use App\Rules\ValidTessieAPIKey;
use Illuminate\Foundation\Http\FormRequest;

class LinkTessieAPIRequest extends FormRequest
{
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
            'config.token' => ['required', 'string', new ValidTessieAPIKey],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'config' => [
                'token' => $this->token
            ]
        ]);
    }
}
