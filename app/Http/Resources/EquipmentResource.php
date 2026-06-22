<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'laboratory_id' => $this->laboratory_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'code' => $this->code,
            'brand' => $this->brand,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'year_acquired' => $this->year_acquired,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'condition' => $this->condition,
            'condition_label' => $this->condition_label,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'photo' => $this->photo,
            'description' => $this->description,
            'notes' => $this->notes,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'laboratory' => LaboratoryResource::make($this->whenLoaded('laboratory')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'items' => EquipmentItemResource::collection($this->whenLoaded('items')),
            'items_baik_count' => $this->whenCounted('items_baik_count'),
            'items_count' => $this->whenCounted('items'),
        ];
    }
}
