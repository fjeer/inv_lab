<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'equipment_id' => $this->equipment_id,
            'sequence_number' => $this->sequence_number,
            'qr_code' => $this->qr_code,
            'condition' => $this->condition,
            'condition_label' => $this->condition_label,
            'condition_color' => $this->condition_color,
            'condition_notes' => $this->condition_notes,
            'last_checked_at' => $this->last_checked_at,
            'last_checked_by' => $this->last_checked_by,
            'replaces_equipment_item_id' => $this->replaces_equipment_item_id,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'equipment' => EquipmentResource::make($this->whenLoaded('equipment')),
            'replaces_equipment_item' => EquipmentItemResource::make($this->whenLoaded('replacesEquipmentItem')),
        ];
    }
}
