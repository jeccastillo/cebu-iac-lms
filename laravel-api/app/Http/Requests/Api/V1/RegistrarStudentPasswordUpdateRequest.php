<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarStudentPasswordUpdateRequest extends FormRequest
{
    /**
     * Authorization is enforced by route middleware (role:registrar,admin).
     * Allow here; rely on middleware for role gating.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for registrar-managed student password update.
     *
     * Body JSON:
     * {
     *   "mode": "generate" | "set",
     *   "new_password": "Minimum8Chars",   // required when mode = "set"
     *   "note": "optional reason or context" // optional, max 500 chars
     * }
     */
    public function rules(): array
    {
        return [
            'mode'         => ['required', 'string', 'in:generate,set'],
            'new_password' => ['required_if:mode,set', 'nullable', 'string', 'min:8', 'max:64'],
            'note'         => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'mode'         => 'mode',
            'new_password' => 'new password',
            'note'         => 'note',
        ];
    }

    public function messages(): array
    {
        return [
            'mode.required'          => 'Mode is required.',
            'mode.in'                => 'Mode must be either "generate" or "set".',
            'new_password.required_if' => 'New password is required when mode is "set".',
            'new_password.min'       => 'Password must be at least 8 characters.',
            'new_password.max'       => 'Password must not exceed 64 characters.',
        ];
    }
}
