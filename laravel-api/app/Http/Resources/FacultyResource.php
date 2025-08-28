<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FacultyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Hides sensitive fields (e.g., strPass) and normalizes legacy columns.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        return [
            'intID'            => (int) $this->intID,
            'strUsername'      => $this->strUsername,
            'strFirstname'     => $this->strFirstname,
            'strMiddlename'    => $this->strMiddlename,
            'strLastname'      => $this->strLastname,
            'strEmail'         => $this->strEmail,
            'strMobileNumber'  => $this->strMobileNumber,
            'strAddress'       => $this->strAddress,
            'strDepartment'    => $this->strDepartment,
            'strSchool'        => $this->strSchool,
            'intUserLevel'     => isset($this->intUserLevel) ? (int) $this->intUserLevel : null,
            'teaching'         => isset($this->teaching) ? (int) $this->teaching : 0,
            'isActive'         => isset($this->isActive) ? (int) $this->isActive : null,
            'strFacultyNumber' => $this->strFacultyNumber ?? null,
            'campus_id'        => isset($this->campus_id) ? (int) $this->campus_id : null,
            'role_codes'       => (array) ($this->role_codes ?? []),
        ];
    }
}
