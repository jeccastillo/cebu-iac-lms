<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClasslistUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is enforced via route middleware (role:registrar,admin)
        return true;
    }

    public function rules(): array
    {
        return [
            // Optional foreign keys
            'intSubjectID' => ['sometimes', 'integer', 'exists:tb_mas_subjects,intID'],
            'intFacultyID' => ['sometimes', 'integer', 'exists:tb_mas_faculty,intID'],

            // Optional term/syid
            'strAcademicYear' => ['sometimes', 'string', 'max:50'],

            // Optional fields
            'strUnits' => ['sometimes', 'nullable', 'string', 'max:20'],
            'intFinalized' => ['sometimes', 'nullable', 'integer'],
            'campus_id' => ['sometimes', 'nullable', 'integer'],
            'sectionCode' => ['sometimes', 'nullable', 'string', 'max:50'],

            // Restricted fields intentionally not accepted/validated:
            // strClassName, year, strSection, sub_section
            // They will be forcibly set to "" by the controller on update.
        ];
    }

    public function messages(): array
    {
        return [
            'intSubjectID.exists' => 'Subject does not exist.',
            'intFacultyID.exists' => 'Faculty does not exist.',
            'strAcademicYear.max' => 'Academic year must not exceed 50 characters.',
            'strUnits.max' => 'Units must not exceed 20 characters.',
        ];
    }
}
