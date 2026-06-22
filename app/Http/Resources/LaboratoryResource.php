<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LaboratoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'room_id' => $this->room_id,
            'location' => $this->location,
            'capacity' => $this->capacity,
            'description' => $this->description,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'room' => RoomResource::make($this->whenLoaded('room')),
            'responsible_person' => UserResource::make($this->whenLoaded('responsiblePerson')),
        ];
    }
}
