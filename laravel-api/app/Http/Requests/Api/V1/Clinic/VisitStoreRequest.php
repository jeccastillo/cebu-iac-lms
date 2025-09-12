<?php

namespace App\Http\Requests\Api\V1\Clinic;

use Illuminate\Foundation\Http\FormRequest;

class VisitStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled via route middleware
        return true;
    }

    public function rules(): array
    {
        return [
            'record_id' => 'required|integer|min:1',
            'visit_date' => 'nullable|date',
            'reason' => 'nullable|string|max:255',

            'triage' => 'nullable|array',
            'triage.bp' => 'nullable|string|max:20',
            'triage.hr' => 'nullable|integer|min:0|max:300',
            'triage.rr' => 'nullable|integer|min:0|max:100',
            'triage.temp_c' => 'nullable|numeric|min:25|max:45',
            'triage.spo2' => 'nullable|integer|min:0|max:100',
            'triage.pain' => 'nullable|integer|min:0|max:10',

            'assessment' => 'nullable|string',
            'diagnosis_codes' => 'nullable|array',
            'diagnosis_codes.*' => 'nullable|string|max:255',
            'treatment' => 'nullable|string',

            'medications_dispensed' => 'nullable|array',
            'medications_dispensed.*.name' => 'required_with:medications_dispensed|string|max:255',
            'medications_dispensed.*.dose' => 'nullable|string|max:255',
            'medications_dispensed.*.qty' => 'nullable|numeric|min:0',
            'medications_dispensed.*.instructions' => 'nullable|string|max:500',

            'follow_up' => 'nullable|string',
            'campus_id' => 'nullable|integer|min:1',
            'created_by' => 'required|integer|min:1',
        ];
    }
}
