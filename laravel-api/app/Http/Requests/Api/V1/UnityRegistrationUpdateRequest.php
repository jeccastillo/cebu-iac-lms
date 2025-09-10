<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UnityRegistrationUpdateRequest extends FormRequest
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
     * Validation rules for editing an existing tb_mas_registration row.
     *
     * Expected payload:
     * {
     *   "student_number": "T25-00-001",
     *   "term": 1,            // tb_mas_sy.intID
     *   "fields": {
     *     "intYearLevel": 1,
     *     "enumStudentType": "continuing",
     *     "current_program": 10,
     *     "current_curriculum": 25,
     *     "tuition_year": 5,
     *     "paymentType": "installment",
     *     "loa_remarks": "",
     *     "withdrawal_period": "before"
     *   }
     * }
     */
    public function rules(): array
    {
        return [
            'student_number' => ['required', 'string'],
            'term'           => ['required', 'integer'],

            'fields' => ['required', 'array', 'min:1'],

            'fields.intYearLevel'    => ['sometimes', 'integer', 'min:1'],
            'fields.enumStudentType' => ['sometimes', 'string', 'in:continuing,new,returnee,transfer'],

            'fields.current_program'    => ['sometimes', 'integer', 'exists:tb_mas_programs,intProgramID'],
            'fields.current_curriculum' => ['sometimes', 'integer', 'exists:tb_mas_curriculum,intID'],
            'fields.tuition_year'       => ['sometimes', 'integer', 'exists:tb_mas_tuition_year,intID'],
            'fields.tuition_installment_plan_id' => ['sometimes', 'nullable', 'integer', 'exists:tb_mas_tuition_year_installment,id'],

            'fields.paymentType'      => ['sometimes', 'nullable', 'string', 'max:50'],
            'fields.loa_remarks'      => ['sometimes', 'nullable', 'string', 'max:1000'],
            'fields.withdrawal_period'=> ['sometimes', 'nullable', 'string', 'in:before,start,end'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_number' => 'student number',
            'term'           => 'term',
            'fields'         => 'fields',
            'fields.intYearLevel'    => 'year level',
            'fields.enumStudentType' => 'student type',
            'fields.current_program' => 'current program',
            'fields.current_curriculum' => 'current curriculum',
            'fields.tuition_year'    => 'tuition year',
            'fields.tuition_installment_plan_id' => 'tuition installment plan',
            'fields.paymentType'     => 'payment type',
            'fields.loa_remarks'     => 'LOA remarks',
            'fields.withdrawal_period' => 'withdrawal period',
        ];
    }

    public function messages(): array
    {
        return [
            'fields.required' => 'At least one field is required to update the registration.',
        ];
    }
}
