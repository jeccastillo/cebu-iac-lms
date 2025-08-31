<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class PaymentDetailUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware handles role authorization (admin-only).
        return true;
    }

    public function rules(): array
    {
        return [
            // Core editable fields (all optional for PATCH semantics)
            'description'        => ['sometimes', 'string', 'max:255'],
            'subtotal_order'     => ['sometimes', 'numeric', 'gt:0'],
            'total_amount_due'   => ['sometimes', 'numeric', 'gte:subtotal_order'],
            'status'             => ['sometimes', 'string', 'max:32'],
            'remarks'            => ['sometimes', 'string', 'max:255'],

            // Method variants — service will map to the actual column (method/payment_method)
            'method'             => ['sometimes', 'string', 'max:64'],
            'payment_method'     => ['sometimes', 'string', 'max:64'],

            // Mode of payment ID (when column exists; validation still ok if table exists)
            'mode_of_payment_id' => ['sometimes', 'integer', 'exists:payment_modes,id'],

            // Posted date will be mapped to paid_at/date/created_at depending on the schema
            'posted_at'          => ['sometimes', 'date'],

            // Number columns — service enforces uniqueness and mapping
            'or_no'              => ['sometimes', 'string', 'max:64'],
            'or_number'          => ['sometimes', 'string', 'max:64'],
            'invoice_number'     => ['sometimes', 'string', 'max:64'],
        ];
    }

    public function attributes(): array
    {
        return [
            'description'        => 'description',
            'subtotal_order'     => 'amount',
            'total_amount_due'   => 'total amount due',
            'status'             => 'status',
            'remarks'            => 'remarks',
            'method'             => 'payment method',
            'payment_method'     => 'payment method',
            'mode_of_payment_id' => 'mode of payment',
            'posted_at'          => 'posted date',
            'or_no'              => 'OR number',
            'or_number'          => 'OR number',
            'invoice_number'     => 'invoice number',
        ];
    }
}
