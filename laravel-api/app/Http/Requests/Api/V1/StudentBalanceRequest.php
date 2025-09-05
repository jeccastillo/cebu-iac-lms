<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StudentBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for fetching student balances or ledger by student id.
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer'],
        ];
    }
}
