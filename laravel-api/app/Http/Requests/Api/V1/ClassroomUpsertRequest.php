<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ClassroomUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Common rules
        $rules = [
            'enumType'      => ['string', 'in:lecture,laboratory,hrm,pe'],
            'strRoomCode' => ['string'],
            'campus_id'    => ['integer'],
            'description'  => ['string'],
        ];

        if ($this->isMethod('post')) {
            // Create
            $rules['strRoomCode'][] = 'required';
            $rules['enumType'][] = 'required';
            $rules['campus_id'][] = 'required';
        } else {
            // Update (PUT/PATCH): all fields optional when present
            $rules['strRoomCode'][] = 'sometimes';
            $rules['enumType'][] = 'sometimes';
            $rules['campus_id'][] = 'sometimes';
            $rules['description'][] = 'sometimes';
        }

        return $rules;
    }
}
