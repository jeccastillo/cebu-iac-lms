<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ClasslistAssignFacultyBulkRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by route middleware (role:registrar,faculty_admin,admin)
        return true;
    }

    public function rules(): array
    {
        return [
            'term' => ['required', 'integer'],

            'assignments' => ['required', 'array', 'min:1', 'max:500'],
            'assignments.*.classlist_id' => ['required', 'integer', 'exists:tb_mas_classlist,intID'],
            'assignments.*.faculty_id'   => ['required', 'integer', 'exists:tb_mas_faculty,intID'],
        ];
    }

    public function messages(): array
    {
        return [
            'term.required' => 'Term is required.',
            'term.integer'  => 'Term must be an integer.',

            'assignments.required' => 'Assignments array is required.',
            'assignments.array'    => 'Assignments must be an array.',
            'assignments.min'      => 'At least one assignment is required.',
            'assignments.max'      => 'Too many assignments. The maximum is 500 per request.',

            'assignments.*.classlist_id.required' => 'classlist_id is required.',
            'assignments.*.classlist_id.integer'  => 'classlist_id must be an integer.',
            'assignments.*.classlist_id.exists'   => 'classlist_id does not exist.',

            'assignments.*.faculty_id.required' => 'faculty_id is required.',
            'assignments.*.faculty_id.integer'  => 'faculty_id must be an integer.',
            'assignments.*.faculty_id.exists'   => 'faculty_id does not exist.',
        ];
    }
}
