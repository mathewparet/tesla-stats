<?php
namespace App\Rules;

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
        ];
    }
}