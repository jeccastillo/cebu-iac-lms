<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentChecklistItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        $subject = $this->whenLoaded('subject');

        return [
            'id'           => (int) $this->intID,
            'intChecklistID'=> (int) $this->intChecklistID,
            'intSubjectID' => (int) $this->intSubjectID,
            'intYearLevel' => $this->intYearLevel !== null ? (int) $this->intYearLevel : null,
            'intSem'       => $this->intSem !== null ? (int) $this->intSem : null,
            'subject'      => $subject ? [
                'id'          => (int) ($subject->intID ?? 0),
                'code'        => $subject->strCode ?? null,
                'description' => $subject->strDescription ?? null,
                'units'       => isset($subject->strUnits) ? (int) $subject->strUnits : null,
            ] : null,
            'strStatus'    => $this->strStatus,
            'dteCompleted' => $this->dteCompleted,
            'isRequired'   => (bool) $this->isRequired,
            'created_at'   => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at'   => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
