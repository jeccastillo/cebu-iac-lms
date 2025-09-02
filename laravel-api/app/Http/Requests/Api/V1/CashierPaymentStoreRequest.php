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
            'student_id'  => ['required', 'integer', 'exists:tb_mas_users,intID'],
            // SYID (School Year ID); per project convention FinanceService reads sy_reference by SYID
            'term'        => ['required', 'integer'],
            // Select which numbering track to use
            'mode'        => ['required', 'in:or,invoice'],
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
