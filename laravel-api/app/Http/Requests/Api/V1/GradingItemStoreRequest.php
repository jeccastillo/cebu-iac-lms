<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GradingItemStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization enforced via route middleware
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => ['required', 'numeric'],
            'remarks' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'Value is required',
            'value.numeric' => 'Value must be numeric',
            'remarks.required' => 'Remarks is required',
        ];
    }
}
