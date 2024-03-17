<?php
namespace App\Rules;

use DateTimeZone;
use Illuminate\Foundation\Http\FormRequest;

class BillingProfileRules extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'name' => 'required|string|min:3',
            'bill_day' => 'required|numeric|min:1|max:31',
            'activated_on' => 'required|date',
            'deactivated_on' => 'nullable|date|after:activated_on',
            'timezone' => 'required|string|in:'.implode(',', DateTimeZone::listIdentifiers()),
            'vehicles.*' => 'nullable|exists:vehicles,id',
            'address' => 'required|string',
            'latitude' => ['required', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'longitude' => ['required', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'radius' => 'required|numeric|min:0',
            'currency' => 'required|string|min:1',
        ];
    }
}