<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcurementItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'procurement_id' => $this->procurement_id,
            'item_name' => $this->item_name,
            'specification' => $this->specification,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'estimated_price' => $this->estimated_price,
            'subtotal' => $this->subtotal,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'replaces_equipment' => EquipmentResource::make($this->whenLoaded('replacesEquipment')),
            'replaces_equipment_item' => EquipmentItemResource::make($this->whenLoaded('replacesEquipmentItem')),
        ];
    }
}
