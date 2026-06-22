<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcurementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'requested_by' => $this->requested_by,
            'approved_by' => $this->approved_by,
            'procurement_number' => $this->procurement_number,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'priority_label' => $this->priority_label,
            'total_estimated_cost' => $this->total_estimated_cost,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'rejection_reason' => $this->rejection_reason,
            'approved_at' => $this->approved_at,
            'notes' => $this->notes,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'requester' => UserResource::make($this->whenLoaded('requester')),
            'approver' => UserResource::make($this->whenLoaded('approver')),
            'items' => ProcurementItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
