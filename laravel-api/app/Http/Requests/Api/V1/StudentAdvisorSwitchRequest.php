<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StudentAdvisorSwitchRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is enforced by route middleware('role:faculty_admin,admin')
        return true;
    }

    public function rules(): array
    {
        return [
            'from_advisor_id' => ['required', 'integer', 'min:1'],
            'to_advisor_id'   => ['required', 'integer', 'min:1', 'different:from_advisor_id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'from_advisor_id' => 'from advisor',
            'to_advisor_id'   => 'to advisor',
        ];
    }
}
