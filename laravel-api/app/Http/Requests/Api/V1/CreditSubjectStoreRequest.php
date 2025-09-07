<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreditSubjectStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by middleware 'role:registrar,admin'
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id'       => ['required', 'integer', 'exists:tb_mas_subjects,intID'],
            'term_taken'       => ['nullable', 'string', 'max:100'],
            'school_taken'     => ['nullable', 'string', 'max:255'],
            'remarks'          => ['nullable', 'string', 'max:255'],
            'floatFinalGrade'  => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.required' => 'subject_id is required',
            'subject_id.integer'  => 'subject_id must be an integer',
            'subject_id.exists'   => 'subject_id must exist in tb_mas_subjects',
        ];
    }
}
