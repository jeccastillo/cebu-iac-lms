<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StudentLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for looking up a student by portal token.
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
        ];
    }
}
