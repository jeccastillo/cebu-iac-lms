<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by route middleware; allow validation to run.
        return true;
    }

    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'strProgramCode' => [
                'sometimes',
                'string',
                'max:50',
                // Unique among programs, excluding current record by PK column
                Rule::unique('tb_mas_programs', 'strProgramCode')->ignore($id, 'intProgramID'),
            ],
            'strProgramDescription' => ['sometimes', 'string', 'max:255'],
            'strMajor' => ['sometimes', 'nullable', 'string', 'max:100'],
            'type' => ['sometimes', 'string', Rule::in(['college','shs','drive','other'])],
            'school' => ['sometimes', 'nullable', 'string', 'max:100'],
            'short_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'default_curriculum' => ['sometimes', 'nullable', 'integer'],
            'enumEnabled' => ['sometimes', 'integer', Rule::in([0,1])],
            'campus_id' => ['sometimes', 'nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'strProgramCode.unique' => 'Program code already exists.',
            'type.in' => 'Program type must be one of: college, shs, drive, other.',
            'enumEnabled.in' => 'enumEnabled must be 0 or 1.',
        ];
    }
}
