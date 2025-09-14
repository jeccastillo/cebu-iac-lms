<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentDeficiencyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is enforced via route middleware (role:department_admin,admin) and Gates.
        return true;
    }

    public function rules(): array
    {
        $deptCodes = config('departments.codes', [
            'registrar','finance','admissions','building_admin','purchasing','academics','clinic','guidance','osas',
        ]);

        return [
            'student_id'            => ['required_without:student_number', 'nullable', 'integer'],
            'student_number'        => ['required_without:student_id', 'nullable', 'string'],
            'term'                  => ['required', 'integer'], // syid
            'department_code'       => ['required', 'string', 'in:' . implode(',', $deptCodes)],

            // Either select existing PD id or provide a new PD payload
            'payment_description_id'         => ['required_without:new_payment_description', 'nullable', 'integer'],
            'new_payment_description'        => ['required_without:payment_description_id', 'nullable', 'array'],
            'new_payment_description.name'   => ['required_with:new_payment_description', 'string', 'max:128'],
            'new_payment_description.amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'new_payment_description.campus_id' => ['sometimes', 'nullable', 'integer'],

            'description'           => ['sometimes', 'nullable', 'string', 'max:255'], // required when no PD selected
            'amount'                => ['sometimes', 'nullable', 'numeric', 'not_in:0'], // defaults from PD if omitted
            'posted_at'             => ['sometimes', 'nullable', 'date'],
            'remarks'               => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_id'                     => 'student id',
            'student_number'                 => 'student number',
            'term'                           => 'term (syid)',
            'department_code'                => 'department',
            'payment_description_id'         => 'payment description',
            'new_payment_description'        => 'new payment description',
            'new_payment_description.name'   => 'new payment description name',
            'new_payment_description.amount' => 'new payment description amount',
            'amount'                         => 'amount',
            'posted_at'                      => 'posted at',
            'remarks'                        => 'remarks',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        // Normalize department code to lowercase
        if (isset($data['department_code'])) {
            $data['department_code'] = strtolower(trim((string) $data['department_code']));
        }

        // Trim student_number if present
        if (isset($data['student_number'])) {
            $data['student_number'] = trim((string) $data['student_number']);
        }

        // Ensure amount as float when provided
        if (array_key_exists('amount', $data) && $data['amount'] !== null && $data['amount'] !== '') {
            $data['amount'] = (float) $data['amount'];
        }

        return $data;
    }
}
