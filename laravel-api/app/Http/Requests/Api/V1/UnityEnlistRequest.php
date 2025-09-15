<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UnityEnlistRequest extends FormRequest
{
    /**
     * Authorize the request.
     * Route will be additionally protected by role middleware (registrar,admin).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for registrar enlistment operations.
     *
     * Payload shape:
     * {
     *   "student_number": "T25-00-001",
     *   "term": 1,                         // tb_mas_sy.intID
     *   "year_level": 1,
     *   "student_type": "continuing",      // optional
     *   "operations": [
     *     { "type": "add", "classlist_id": 123 },
     *     { "type": "drop", "classlist_id": 456 },
     *     { "type": "change_section", "from_classlist_id": 111, "to_classlist_id": 222 }
     *   ]
     * }
     */
    public function rules(): array
    {
        return [
            'student_number' => ['required', 'string'],
            'term'           => ['required', 'integer'],
            'year_level'     => ['required', 'integer', 'min:1'],
            'student_type'   => ['sometimes', 'string', 'in:continuing,new,returnee,transfer,shiftee'],

            'operations'                 => ['required', 'array', 'min:1'],
            'operations.*.type'          => ['required', 'string', 'in:add,drop,change_section'],

            // For add and drop operations
            'operations.*.classlist_id'  => ['required_if:operations.*.type,add,drop', 'integer'],

            // For change_section operation
            'operations.*.from_classlist_id' => ['required_if:operations.*.type,change_section', 'integer'],
            'operations.*.to_classlist_id'   => ['required_if:operations.*.type,change_section', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_number' => 'student number',
            'term' => 'term',
            'year_level' => 'year level',
            'operations' => 'operations',
            'operations.*.type' => 'operation type',
            'operations.*.classlist_id' => 'classlist id',
            'operations.*.from_classlist_id' => 'from classlist id',
            'operations.*.to_classlist_id' => 'to classlist id',
        ];
    }

    public function messages(): array
    {
        return [
            'operations.*.classlist_id.required_if' => 'The classlist id is required for add and drop operations.',
            'operations.*.from_classlist_id.required_if' => 'The from classlist id is required for change_section operations.',
            'operations.*.to_classlist_id.required_if' => 'The to classlist id is required for change_section operations.',
        ];
    }
}
