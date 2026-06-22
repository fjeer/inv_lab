<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DamageReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'equipment_id' => $this->equipment_id,
            'equipment_item_id' => $this->equipment_item_id,
            'reported_by' => $this->reported_by,
            'handled_by' => $this->handled_by,
            'damage_type' => $this->damage_type,
            'description' => $this->description,
            'incident_date' => $this->incident_date,
            'photo' => $this->photo,
            'status' => $this->status,
            'repair_cost' => $this->repair_cost,
            'repair_notes' => $this->repair_notes,
            'resolved_at' => $this->resolved_at,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'equipment' => EquipmentResource::make($this->whenLoaded('equipment')),
            'equipment_item' => EquipmentItemResource::make($this->whenLoaded('equipmentItem')),
            'reporter' => UserResource::make($this->whenLoaded('reporter')),
            'handler' => UserResource::make($this->whenLoaded('handler')),
        ];
    }
}
