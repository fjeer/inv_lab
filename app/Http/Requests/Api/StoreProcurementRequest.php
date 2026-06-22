<?php

namespace App\Http\Requests\Api;

use App\Models\Procurement;
use Illuminate\Foundation\Http\FormRequest;

class StoreProcurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Procurement::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.replaces_equipment_item_id' => ['nullable', 'exists:equipment_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.estimated_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function toDto(int $userId): \App\Support\DTOs\CreateProcurementData
    {
        return \App\Support\DTOs\CreateProcurementData::fromRequest($this->validated(), $userId);
    }
}
