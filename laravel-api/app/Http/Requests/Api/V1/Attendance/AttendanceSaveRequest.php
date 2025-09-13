<?php

namespace App\Http\Requests\Api\V1\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is enforced by controller via Gate + header fallbacks
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.intCSID' => ['required', 'integer', 'min:1'],
            'items.*.is_present' => ['nullable', 'boolean'],
            'items.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Items array is required.',
            'items.array' => 'Items must be an array.',
            'items.min' => 'At least one item is required.',
            'items.*.intCSID.required' => 'Each item must include intCSID.',
            'items.*.intCSID.integer' => 'intCSID must be an integer.',
            'items.*.intCSID.min' => 'intCSID must be greater than zero.',
            'items.*.is_present.boolean' => 'is_present must be true, false, or null.',
            'items.*.remarks.string' => 'remarks must be a string.',
            'items.*.remarks.max' => 'remarks must not exceed 255 characters.',
        ];
    }
}
