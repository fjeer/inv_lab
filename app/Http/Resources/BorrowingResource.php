<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'laboratory_id' => $this->laboratory_id,
            'user_id' => $this->user_id,
            'approved_by' => $this->approved_by,
            'purpose' => $this->purpose,
            'activity_type' => $this->activity_type,
            'borrow_date' => $this->borrow_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'rejection_reason' => $this->rejection_reason,
            'notes' => $this->notes,
            'approved_at' => $this->approved_at,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => UserResource::make($this->whenLoaded('user')),
            'laboratory' => LaboratoryResource::make($this->whenLoaded('laboratory')),
            'approver' => UserResource::make($this->whenLoaded('approver')),
        ];
    }
}
