<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UnityResetRegistrationRequest extends FormRequest
{
    /**
     * Authorize the request.
     * Route will be protected by role middleware (registrar,admin).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for reset registration operation.
     *
     * Payload shape:
     * {
     *   "student_number": "T25-00-001",
     *   "term": 1,            // optional, tb_mas_sy.intID; defaults to active term when omitted
     *   "password": "..."     // optional; collected by UI for confirmation; not enforced by backend
     * }
     */
    public function rules(): array
    {
        return [
            'student_number' => ['required', 'string'],
            'term'           => ['sometimes', 'integer'],
            'password'       => ['required', 'string', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_number' => 'student number',
            'term'           => 'term',
            'password'       => 'password',
        ];
    }

    public function messages(): array
    {
        return [
            // No custom messages required for now
        ];
    }
}
