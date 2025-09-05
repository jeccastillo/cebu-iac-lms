<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceGenerateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Protected via role middleware in routes (finance,admin)
        return true;
    }

    public function rules(): array
    {
        return [
            'type'         => ['required', 'string', 'in:tuition,billing,other'],
            'student_id'   => ['required', 'integer'],
            'term'         => ['required', 'integer'], // syid

            // Optional invoice content
            'items'        => ['sometimes', 'array'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.amount'      => ['required_with:items', 'numeric'],

            // Optional overrides/meta
            'amount'          => ['sometimes', 'numeric'],
            'status'          => ['sometimes', 'string', 'in:Draft,Issued,Paid,Void'],
            'posted_at'       => ['sometimes', 'nullable', 'date'],
            'due_at'          => ['sometimes', 'nullable', 'date'],
            'remarks'         => ['sometimes', 'nullable', 'string'],
            'campus_id'       => ['sometimes', 'nullable', 'integer'],
            'cashier_id'      => ['sometimes', 'nullable', 'integer'],
            'registration_id' => ['sometimes', 'nullable', 'integer'],
            'invoice_number'  => ['sometimes', 'nullable', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type'            => 'invoice type',
            'student_id'      => 'student id',
            'term'            => 'term (syid)',
            'items'           => 'items',
            'amount'          => 'amount',
            'posted_at'       => 'posted at',
            'due_at'          => 'due at',
            'remarks'         => 'remarks',
            'campus_id'       => 'campus id',
            'cashier_id'      => 'cashier id',
            'registration_id' => 'registration id',
            'invoice_number'  => 'invoice number',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        // Normalize/alias: syid from term
        $data['syid'] = (int) $data['term'];
        unset($data['term']);

        return $data;
    }
}
