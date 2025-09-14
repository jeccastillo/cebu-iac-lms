<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentDeficiencyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization via route middleware (role:department_admin,admin) and Gates.
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'    => ['sometimes', 'numeric', 'not_in:0'],
            'posted_at' => ['sometimes', 'nullable', 'date'],
            'remarks'   => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'amount'    => 'amount',
            'posted_at' => 'posted at',
            'remarks'   => 'remarks',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        if (array_key_exists('amount', $data) && $data['amount'] !== null && $data['amount'] !== '') {
            $data['amount'] = (float) $data['amount'];
        }
        return $data;
    }
}
