<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayeeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Protected via route middleware (role: admin, finance_admin)
        return true;
    }

    public function rules(): array
    {
        $id = (int) ($this->route('id') ?? 0);

        return [
            'id_number'      => [
                'required',
                'string',
                'max:40',
                Rule::unique('tb_mas_payee', 'id_number')->ignore($id, 'id'),
            ],
            'firstname'      => ['required', 'string', 'max:99'],
            'lastname'       => ['sometimes', 'nullable', 'string', 'max:99'],
            'middlename'     => ['sometimes', 'nullable', 'string', 'max:99'],
            'tin'            => ['sometimes', 'nullable', 'string', 'max:99'],
            'address'        => ['sometimes', 'nullable', 'string'],
            'contact_number' => ['sometimes', 'nullable', 'string', 'max:40'],
            'email'          => ['required', 'email', 'max:99'],
        ];
    }

    public function attributes(): array
    {
        return [
            'id_number'      => 'ID number',
            'firstname'      => 'first name',
            'lastname'       => 'last name',
            'middlename'     => 'middle name',
            'tin'            => 'TIN',
            'address'        => 'address',
            'contact_number' => 'contact number',
            'email'          => 'email address',
        ];
    }
}
