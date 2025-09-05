<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CashierUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by route middleware (role:cashier_admin,admin)
        return true;
    }

    public function rules(): array
    {
        return [
            'temporary_admin' => ['sometimes', 'boolean'],
            'or_current'      => ['sometimes', 'integer', 'min:0'],
            'invoice_current' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
