<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CashierAssignRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is enforced by route middleware (role:cashier_admin,admin)
        return true;
    }

    public function rules(): array
    {
        return [
            'faculty_id' => ['required', 'integer', 'exists:tb_mas_faculty,intID'],
        ];
    }
}
