<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConditionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'equipment_id' => $this->equipment_id,
            'equipment_item_id' => $this->equipment_item_id,
            'checked_by' => $this->checked_by,
            'condition' => $this->condition,
            'previous_condition' => $this->previous_condition,
            'check_date' => $this->check_date,
            'description' => $this->description,
            'action_taken' => $this->action_taken,
            'photo' => $this->photo,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'equipment' => EquipmentResource::make($this->whenLoaded('equipment')),
            'equipment_item' => EquipmentItemResource::make($this->whenLoaded('equipmentItem')),
            'checker' => UserResource::make($this->whenLoaded('checker')),
        ];
    }
}
