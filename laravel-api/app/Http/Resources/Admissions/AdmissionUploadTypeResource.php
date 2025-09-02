<?php

namespace App\Http\Resources\Admissions;

use Illuminate\Http\Resources\Json\JsonResource;

class AdmissionUploadTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'key' => $this->key,
            'file' => $this->file,
            'order' => $this->order,
            'is_loading' => $this->is_loading,
            'required' => $this->required
        ];
    }
}
