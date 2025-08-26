<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarClasslistOpRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Gate/Policies can be added later; allow for now.
        return true;
    }

    public function rules(): array
    {
        return [
            'classlist_id'   => 'required|integer',
            'student_number' => 'required|string',
            'action'         => 'required|in:drop,add,shift,revert',
            'reason'         => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'classlist_id.required'   => 'Classlist ID is required.',
            'classlist_id.integer'    => 'Classlist ID must be an integer.',
            'student_number.required' => 'Student number is required.',
            'action.required'         => 'Action is required.',
            'action.in'               => 'Action must be one of: drop, add, shift, revert.',
            'reason.max'              => 'Reason must not exceed 255 characters.',
        ];
    }
}
