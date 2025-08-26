ddd<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class FinanceTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policies/guards can be added later; allow for now.
        return true;
    }

    public function rules(): array
    {
        return [
            'student_number' => 'required|string',
            'type'           => 'required|in:payment,adjustment,refund,charge',
            'amount'         => 'required|numeric|min:0.01',
            'method'         => 'nullable|in:cash,check,online,transfer,other',
            'remarks'        => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'student_number.required' => 'Student number is required.',
            'type.required'           => 'Transaction type is required.',
            'type.in'                 => 'Type must be one of: payment, adjustment, refund, charge.',
            'amount.required'         => 'Amount is required.',
            'amount.numeric'          => 'Amount must be numeric.',
            'amount.min'              => 'Amount must be at least 0.01.',
            'method.in'               => 'Method must be one of: cash, check, online, transfer, other.',
            'remarks.max'             => 'Remarks must not exceed 255 characters.',
        ];
    }
}
