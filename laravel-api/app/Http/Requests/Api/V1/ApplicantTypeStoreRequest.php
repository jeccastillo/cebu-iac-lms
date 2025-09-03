<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicantTypeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization via route middleware ('role:admissions,admin')
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique composite on (name, type)
                Rule::unique('tb_mas_applicant_types', 'name')->where(function ($q) use ($type) {
                    return $q->where('type', $type);
                }),
            ],
            'type' => ['required', Rule::in(['college', 'shs', 'grad'])],
            'sub_type' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'applicant type name',
            'type' => 'applicant category',
            'sub_type' => 'applicant sub type',
        ];
    }
}
