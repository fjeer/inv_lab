<?php

namespace App\Http\Requests\Api;

use App\Models\Equipment;
use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Equipment::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'category_id' => ['required', 'exists:equipment_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:equipment,code'],
            'quantity' => ['required', 'integer', 'min:0'],
            'condition' => ['required', 'in:baik,rusak_ringan,rusak_berat,hilang'],
            'status' => ['required', 'in:available,in_use,borrowed,maintenance,disposed'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'year_acquired' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'price' => ['nullable', 'numeric', 'min:0'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function toDto(): \App\Support\DTOs\CreateEquipmentData
    {
        return \App\Support\DTOs\CreateEquipmentData::fromRequest($this->validated());
    }
}
