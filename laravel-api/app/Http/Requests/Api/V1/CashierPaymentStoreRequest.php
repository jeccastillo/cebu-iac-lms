<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CashierPaymentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Access is enforced via role middleware at the route level.
        return true;
    }

    public function rules(): array
    {
        return [
            // Either student_id or payee_id is required
            'student_id'  => ['required_without:payee_id', 'nullable', 'integer', 'exists:tb_mas_users,intID'],
            'payee_id'    => ['required_without:student_id', 'nullable', 'integer', 'exists:tb_mas_payee,id'],

            // When paying as payee, enforce id_number match
            'id_number'   => ['required_with:payee_id', 'string', 'max:40'],

            // Term (syid) is required for student payments; omitted for tenants
            'term'        => ['nullable', 'integer', 'required_without:payee_id'],

            // Select which numbering track to use (allow 'none' for encoding without number)
            'mode'        => ['required', 'in:or,invoice,none'],

            // Monetary amount to be stored in payment_details.subtotal_order
            'amount'      => ['required', 'numeric', 'gt:0'],

            // e.g. "Tuition Payment", "Reservation Payment"
            'description' => ['required', 'string', 'max:255'],

            // Required: selected payment mode from payment_modes table
            'mode_of_payment_id' => ['required', 'integer', 'exists:payment_modes,id'],

            // Optional payment method; will map to method or payment_method column as available
            'method'      => ['nullable', 'string', 'max:100'],

            // Required remarks per instruction
            'remarks'     => ['required', 'string', 'max:1000'],

            // Optional; will map to paid_at/date/created_at depending on available columns
            'posted_at'   => ['nullable', 'date'],

            // Optional campus; when present and column exists will map to payment_details.student_campus
            'campus_id'   => ['nullable', 'integer'],

            // Optional fields for invoice-linking when mode='or'
            // When provided, backend will validate remaining amount on the referenced invoice_number.
            'invoice_id'     => ['sometimes', 'nullable', 'integer'],
            'invoice_number' => ['sometimes', 'nullable', 'integer'],

            // Optional legacy fields already consumed by controller
            'or_date'         => ['sometimes', 'nullable', 'date'],
            'convenience_fee' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }
}
