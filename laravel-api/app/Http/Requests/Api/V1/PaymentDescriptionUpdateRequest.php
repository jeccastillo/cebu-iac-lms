<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentDescriptionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Route parameter name is {id} in routes
        $id = (int) $this->route('id');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:128',
                Rule::unique('payment_descriptions', 'name')->ignore($id, 'intID'),
            ],
            'amount' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],
            'campus_id' => [
                'sometimes',
                'nullable',
                'integer',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required when provided.',
            'name.unique' => 'The name has already been taken.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be at least 0.',
        ];
    }
}
