<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviousSchoolStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled via route middleware('role:admissions,admin')
        return true;
    }

    public function rules(): array
    {
        $city = $this->input('city');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // unique on (name, city)
                Rule::unique('previous_schools', 'name')->where(function ($q) use ($city) {
                    return $q->where('city', $city);
                }),
            ],
            'city' => ['nullable', 'string', 'max:128'],
            'province' => ['nullable', 'string', 'max:128'],
            'country' => ['nullable', 'string', 'max:128'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'school name',
            'city' => 'city',
            'province' => 'province',
            'country' => 'country',
        ];
    }
}
