<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by route middleware; allow validation to run.
        return true;
    }

    public function rules(): array
    {
        return [
            'strProgramCode' => [
                'required',
                'string',
                'max:50',
                // Unique among all programs by code (legacy table/column names)
                Rule::unique('tb_mas_programs', 'strProgramCode'),
            ],
            'strProgramDescription' => ['required', 'string', 'max:255'],
            'strMajor' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'string', Rule::in(['college','shs','drive','other'])],
            'school' => ['nullable', 'string', 'max:100'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'default_curriculum' => ['nullable', 'integer'],
            'enumEnabled' => ['nullable', 'integer', Rule::in([0,1])],
            // campus_id presence varies by migration; validate as integer if provided
            'campus_id' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'strProgramCode.required' => 'Program code is required.',
            'strProgramCode.unique' => 'Program code already exists.',
            'strProgramDescription.required' => 'Program description is required.',
            'type.required' => 'Program type is required.',
            'type.in' => 'Program type must be one of: college, shs, drive, other.',
            'enumEnabled.in' => 'enumEnabled must be 0 or 1.',
        ];
    }
}
