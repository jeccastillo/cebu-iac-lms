<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class NonStudentPaymentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Access is enforced via route middleware (role:cashier_admin,finance,admin)
        return true;
    }

    public function rules(): array
    {
        return [
            // Strictly non-student flow
            'payee_id'    => ['required', 'integer', 'exists:tb_mas_payee,id'],
            'id_number'   => ['required', 'string', 'max:40'],

            // Disallow student fields on this endpoint (if sent, ignore at controller or treat as validation error)
            'student_id'  => ['prohibited'],
            'term'        => ['prohibited'],

            // Select which numbering track to use (allow 'none' for encoding without number)
            'mode'        => ['required', 'in:or,invoice,none'],

            // Monetary amount to be stored in payment_details.subtotal_order
            'amount'      => ['required', 'numeric', 'gt:0'],

            // e.g. "Walk-in Payment", "Facility Rental", "Others"
            'description' => ['required', 'string', 'max:255'],

            // Required: selected payment mode from payment_modes table
            'mode_of_payment_id' => ['required', 'integer', 'exists:payment_modes,id'],

            // Optional payment method; will map to method/pmethod column as available
            'method'      => ['sometimes', 'nullable', 'string', 'max:100'],

            // Required remarks
            'remarks'     => ['required', 'string', 'max:1000'],

            // Optional; will map to paid_at/date/created_at depending on available columns
            'posted_at'   => ['sometimes', 'nullable', 'date'],

            // Optional campus; when present and column exists will map to payment_details.student_campus
            'campus_id'   => ['sometimes', 'nullable', 'integer'],

            // Optional fields for invoice-linking when mode='or' or 'invoice'
            // When provided, backend will validate remaining amount on the referenced invoice_number (best-effort).
            'invoice_id'     => ['sometimes', 'nullable', 'integer'],
            'invoice_number' => ['sometimes', 'nullable', 'integer'],

            // Optional legacy fields already consumed by controller
            'or_date'         => ['sometimes', 'nullable', 'date'],
            'convenience_fee' => ['sometimes', 'nullable', 'numeric', 'min:0'],

            // Optional explicit number assignment support (rare; primarily sequence-based via cashier)
            'number'          => ['sometimes', 'nullable', 'integer'],
        ];
    }
}
