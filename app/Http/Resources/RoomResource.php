<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'building_id' => $this->building_id,
            'name' => $this->name,
            'code' => $this->code,
            'floor' => $this->floor,
            'description' => $this->description,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'building' => BuildingResource::make($this->whenLoaded('building')),
        ];
    }
}
