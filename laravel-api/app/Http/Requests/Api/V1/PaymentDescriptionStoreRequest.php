<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class PaymentDescriptionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:128', 'unique:payment_descriptions,name'],
            'amount'     => ['nullable', 'numeric', 'min:0'],
            'campus_id'  => ['sometimes', 'nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'The name field is required.',
            'name.unique'    => 'The name has already been taken.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min'     => 'Amount must be at least 0.',
        ];
    }
}
