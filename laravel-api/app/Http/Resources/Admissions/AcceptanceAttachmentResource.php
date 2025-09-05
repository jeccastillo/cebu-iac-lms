<?php

namespace App\Http\Resources\Admissions;

use Illuminate\Http\Resources\Json\JsonResource;

class AcceptanceAttachmentResource extends JsonResource
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
            'filename' => $this->filename,
            'orig_filename' => $this->orig_filename,
            'filetype' => $this->filetype,
            'url' => $this->url
        ];
    }
}
