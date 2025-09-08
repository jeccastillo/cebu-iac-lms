<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StudentBillingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled by role middleware (finance,admin)
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id'                  => ['required', 'integer'],
            'term'                        => ['required', 'integer'], // syid
            'description'                 => ['required', 'string', 'max:255'],
            'amount'                      => ['required', 'numeric', 'not_in:0'],
            'posted_at'                   => ['sometimes', 'nullable', 'date'],
            'remarks'                     => ['sometimes', 'nullable', 'string'],
            'generate_invoice'            => ['sometimes', 'boolean'],
            // Optional invoice fields when generating invoice now
            'withholding_tax_percentage'  => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'invoice_amount'              => ['sometimes', 'nullable', 'numeric'],
            'invoice_amount_ves'          => ['sometimes', 'nullable', 'numeric'],
            'invoice_amount_vzrs'         => ['sometimes', 'nullable', 'numeric'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_id'                 => 'student id',
            'term'                      => 'term (syid)',
            'description'               => 'description',
            'amount'                    => 'amount',
            'posted_at'                 => 'posted at',
            'remarks'                   => 'remarks',
            'generate_invoice'          => 'generate invoice',
            'withholding_tax_percentage'=> 'Less EWT (%)',
            'invoice_amount'            => 'Vatable Amount',
            'invoice_amount_ves'        => 'Vat Exempt Tax',
            'invoice_amount_vzrs'       => 'Vat Zero Rated Sales',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        // Normalize keys to internal names
        $data['intStudentID'] = (int) $data['student_id'];
        $data['syid']         = (int) $data['term'];

        // Unset external aliases
        unset($data['student_id'], $data['term']);

        // Default generate_invoice to true when not provided; cast to boolean when present
        $data['generate_invoice'] = array_key_exists('generate_invoice', $data)
            ? (bool) $data['generate_invoice']
            : true;

        return $data;
    }
}
