<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatrolScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'laboratory_id' => $this->laboratory_id,
            'day_of_week' => $this->day_of_week,
            'day_label' => $this->day_label,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'notes' => $this->notes,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => UserResource::make($this->whenLoaded('user')),
            'laboratory' => LaboratoryResource::make($this->whenLoaded('laboratory')),
        ];
    }
}
