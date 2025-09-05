<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CashierPaymentAssignNumberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:or,invoice'],
            'number' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
