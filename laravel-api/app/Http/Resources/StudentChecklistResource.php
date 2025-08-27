<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\StudentChecklistService;

class StudentChecklistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        $service = app(StudentChecklistService::class);
        $summary = $service->computeSummary($this->resource);

        return [
            'id'              => (int) $this->intID,
            'intStudentID'    => (int) $this->intStudentID,
            'intCurriculumID' => (int) $this->intCurriculumID,
            'remarks'         => $this->remarks,
            'created_by'      => $this->created_by ? (int) $this->created_by : null,
            'created_at'      => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at'      => $this->updated_at ? $this->updated_at->toDateTimeString() : null,

            'summary'         => $summary,

            'items'           => StudentChecklistItemResource::collection(
                $this->whenLoaded('items')
            ),
        ];
    }
}
