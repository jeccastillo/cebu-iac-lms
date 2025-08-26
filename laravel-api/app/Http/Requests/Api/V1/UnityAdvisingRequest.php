<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UnityAdvisingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policies/guards can be enforced later; allow for now.
        return true;
    }

    public function rules(): array
    {
        return [
            'student_number'        => 'required|string',
            'program_id'            => 'required|integer',
            'term'                  => 'required|string',
            'subjects'              => 'required|array|min:1',
            'subjects.*.subject_id' => 'required|integer',
            'subjects.*.section'    => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'student_number.required' => 'Student number is required.',
            'program_id.required'     => 'Program ID is required.',
            'program_id.integer'      => 'Program ID must be an integer.',
            'term.required'           => 'Term is required.',
            'subjects.required'       => 'At least one subject must be provided.',
            'subjects.array'          => 'Subjects must be an array.',
            'subjects.min'            => 'Provide at least one subject.',
            'subjects.*.subject_id.required' => 'Each subject must have a subject_id.',
            'subjects.*.subject_id.integer'  => 'Each subject_id must be an integer.',
        ];
    }
}
