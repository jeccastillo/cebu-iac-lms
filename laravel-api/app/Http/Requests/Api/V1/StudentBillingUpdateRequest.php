<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StudentBillingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by role middleware (finance,admin)
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'string', 'max:255'],
            'amount'      => ['sometimes', 'numeric', 'not_in:0'],
            'posted_at'   => ['sometimes', 'nullable', 'date'],
            'remarks'     => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'description' => 'description',
            'amount'      => 'amount',
            'posted_at'   => 'posted at',
            'remarks'     => 'remarks',
        ];
    }
}
