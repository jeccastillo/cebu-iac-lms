<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware enforces admin-only access
        return true;
    }

    public function rules(): array
    {
        return [
            'status'         => ['sometimes', 'string', 'in:Draft,Issued,Paid,Void'],
            'posted_at'      => ['sometimes', 'nullable', 'date'],
            'due_at'         => ['sometimes', 'nullable', 'date'],
            'remarks'        => ['sometimes', 'nullable', 'string'],
            'campus_id'      => ['sometimes', 'nullable', 'integer'],
            'cashier_id'     => ['sometimes', 'nullable', 'integer'],
            'invoice_number' => ['sometimes', 'nullable', 'integer'],
            'amount'         => ['sometimes', 'numeric'],
            'payload'        => ['sometimes', 'array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'status'         => 'status',
            'posted_at'      => 'posted at',
            'due_at'         => 'due at',
            'remarks'        => 'remarks',
            'campus_id'      => 'campus id',
            'cashier_id'     => 'cashier id',
            'invoice_number' => 'invoice number',
            'amount'         => 'amount',
            'payload'        => 'payload',
        ];
    }
}
