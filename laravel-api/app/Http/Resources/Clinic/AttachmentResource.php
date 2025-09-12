<?php

namespace App\Http\Resources\Clinic;

use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    public function toArray($request): array
    {
        $res = is_array($this->resource) ? $this->resource : (array) ($this->resource instanceof \JsonSerializable ? $this->resource->jsonSerialize() : $this->resource);

        return [
            'id' => $res['id'] ?? ($this->id ?? null),
            'record_id' => $res['record_id'] ?? ($this->record_id ?? null),
            'visit_id' => $res['visit_id'] ?? ($this->visit_id ?? null),
            'original_name' => $res['original_name'] ?? ($this->original_name ?? null),
            'path' => $res['path'] ?? ($this->path ?? null),
            'mime' => $res['mime'] ?? ($this->mime ?? null),
            'size_bytes' => $res['size_bytes'] ?? ($this->size_bytes ?? null),
            'uploaded_by' => $res['uploaded_by'] ?? ($this->uploaded_by ?? null),
            'created_at' => isset($res['created_at']) ? (string) $res['created_at'] : (isset($this->created_at) ? (string) $this->created_at : null),
            'updated_at' => isset($res['updated_at']) ? (string) $res['updated_at'] : (isset($this->updated_at) ? (string) $this->updated_at : null),
        ];
    }
}
