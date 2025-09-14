<?php

namespace App\Http\Requests\Api\V1\Payments;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_information_id' => ['required', 'integer'],
            'student_number' => ['nullable', 'string'],
            'first_name' => ['required', 'string'],
            'middle_name' => ['nullable', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'contact_number' => ['required', 'string'],
            'description' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
            'mode_of_payment_id' => ['required', 'integer'],
            'total_price_without_charge' => ['required', 'numeric', 'min:0'],
            'total_price_with_charge' => ['required', 'numeric', 'min:0'],
            'charge' => ['required', 'numeric', 'min:0'],
            'mailing_fee' => ['nullable', 'numeric', 'min:0'],

            'order_items' => ['required', 'array', 'min:1'],
            'order_items.*.id' => ['required', 'integer'],
            'order_items.*.title' => ['required', 'string'],
            'order_items.*.qty' => ['required', 'integer', 'min:1'],
            'order_items.*.price_default' => ['required', 'numeric', 'min:0'],
            'order_items.*.term' => ['nullable', 'string'],
            'order_items.*.academic_year' => ['nullable', 'string'],

            // BDO specific bill_to fields (conditionally required in controller depending on pmethod)
            'bill_to_forename' => ['nullable', 'string'],
            'bill_to_surname' => ['nullable', 'string'],
            'bill_to_email' => ['nullable', 'email'],

            // Optional
            'dob' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_information_id.required' => 'Student information is required.',
            'mode_of_payment_id.required' => 'Mode of payment is required.',
            'order_items.required' => 'At least one order item is required.',
        ];
    }
}
