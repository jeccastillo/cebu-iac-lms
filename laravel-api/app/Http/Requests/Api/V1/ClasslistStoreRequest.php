<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClasslistStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is enforced via route middleware (role:registrar,admin)
        return true;
    }

    public function rules(): array
    {
        return [
            // Required foreign keys
            'intSubjectID' => ['required', 'integer', 'exists:tb_mas_subjects,intID'],
            'intFacultyID' => ['nullable', 'integer', 'exists:tb_mas_faculty,intID'],

            // Required term/syid
            'strAcademicYear' => ['required', 'string', 'max:50'],

            // Optional fields
            'strUnits' => ['nullable', 'string', 'max:20'],
            'intFinalized' => ['nullable', 'integer'],
            'campus_id' => ['nullable', 'integer'],
            'sectionCode' => ['nullable', 'string', 'max:50'],

            // Note: Restricted fields intentionally not accepted here:
            // strClassName, year, strSection, sub_section
            // They will be forcibly set to "" by the controller on create/update.
        ];
    }

    public function messages(): array
    {
        return [
            'intSubjectID.required' => 'Subject is required.',
            'intSubjectID.exists' => 'Subject does not exist.',
            'intFacultyID.exists' => 'Faculty does not exist.',
            'strAcademicYear.required' => 'Academic year (term) is required.',
            'strAcademicYear.max' => 'Academic year must not exceed 50 characters.',
            'strUnits.max' => 'Units must not exceed 20 characters.',
        ];
    }
}
