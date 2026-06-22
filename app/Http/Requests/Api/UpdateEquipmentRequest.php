<?php

namespace App\Http\Requests\Api;

use App\Models\Equipment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('equipment')) ?? false;
    }

    public function rules(): array
    {
        $equipmentId = $this->route('equipment')?->id;

        return [
            'laboratory_id' => ['sometimes', 'exists:laboratories,id'],
            'category_id' => ['sometimes', 'exists:equipment_categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'unique:equipment,code,' . $equipmentId],
            'quantity' => ['sometimes', 'integer', 'min:0'],
            'condition' => ['sometimes', 'in:baik,rusak_ringan,rusak_berat,hilang'],
            'status' => ['sometimes', 'in:available,in_use,borrowed,maintenance,disposed'],
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
}
