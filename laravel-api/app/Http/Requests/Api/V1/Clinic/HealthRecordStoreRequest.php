<?php

namespace App\Http\Requests\Api\V1\Clinic;

use Illuminate\Foundation\Http\FormRequest;

class HealthRecordStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by role middleware at route level
        return true;
    }

    public function rules(): array
    {
        return [
            'person_type' => 'required|string|in:student,faculty',
            'person_student_id' => 'required_if:person_type,student|nullable|integer|min:1',
            'person_faculty_id' => 'required_if:person_type,faculty|nullable|integer|min:1',

            'blood_type' => 'nullable|string|max:3',
            'height_cm' => 'nullable|numeric|min:0|max:300',
            'weight_kg' => 'nullable|numeric|min:0|max:500',

            'allergies' => 'nullable|array',
            'allergies.*.name' => 'required_with:allergies|string|max:255',
            'allergies.*.reaction' => 'nullable|string|max:255',
            'allergies.*.severity' => 'nullable|string|in:mild,moderate,severe',

            'medications' => 'nullable|array',
            'medications.*.name' => 'required_with:medications|string|max:255',
            'medications.*.dose' => 'nullable|string|max:255',
            'medications.*.freq' => 'nullable|string|max:255',
            'medications.*.start_date' => 'nullable|date',
            'medications.*.end_date' => 'nullable|date',
            'medications.*.ongoing' => 'nullable|boolean',

            'immunizations' => 'nullable|array',
            'immunizations.*.name' => 'required_with:immunizations|string|max:255',
            'immunizations.*.date' => 'nullable|date',
            'immunizations.*.lot' => 'nullable|string|max:255',
            'immunizations.*.site' => 'nullable|string|max:255',

            'conditions' => 'nullable|array',
            'conditions.*.name' => 'required_with:conditions|string|max:255',
            'conditions.*.since' => 'nullable|date',
            'conditions.*.status' => 'nullable|string|in:active,resolved',

            'notes' => 'nullable|string',
            'campus_id' => 'nullable|integer|min:1',
        ];
    }
}
