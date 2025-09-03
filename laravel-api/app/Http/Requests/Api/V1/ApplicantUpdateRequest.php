<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ApplicantUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by route middleware('role:admissions,admin')
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'           => ['sometimes', 'string', 'max:100'],
            'middle_name'          => ['sometimes', 'nullable', 'string', 'max:100'],
            'last_name'            => ['sometimes', 'string', 'max:100'],
            'email'                => ['sometimes', 'string', 'email', 'max:150'],
            'mobile_number'        => ['sometimes', 'string', 'max:50'],
            'date_of_birth'        => ['sometimes', 'date'],

            // Waiver controls
            'waive_application_fee'=> ['sometimes', 'boolean'],
            'waive_reason'         => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name'           => 'first name',
            'middle_name'          => 'middle name',
            'last_name'            => 'last name',
            'email'                => 'email',
            'mobile_number'        => 'mobile number',
            'date_of_birth'        => 'date of birth',
            'waive_application_fee'=> 'waive application fee',
            'waive_reason'         => 'waiver reason',
        ];
    }
}
